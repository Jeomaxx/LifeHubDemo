<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/rate_limiter.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Rate limiting - 30 requests per minute for portfolio/resume generation
$rateLimiter = new RateLimiter();
if (!$rateLimiter->checkLimit($_SERVER['REMOTE_ADDR'], 'portfolio_api', 30, 60)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests. Please try again later.']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? $data['action'] ?? '';

try {
    switch ($action) {
        case 'get_data':
            $skills = $db->fetchAll("SELECT * FROM career_skills WHERE user_id = ? ORDER BY proficiency_level DESC", [$userId]) ?: [];
            $projects = $db->fetchAll("SELECT * FROM portfolio_projects WHERE user_id = ? ORDER BY created_at DESC", [$userId]) ?: [];
            $milestones = $db->fetchAll("SELECT * FROM career_milestones WHERE user_id = ? ORDER BY achievement_date DESC", [$userId]) ?: [];
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'skills' => $skills,
                    'projects' => $projects,
                    'milestones' => $milestones
                ]
            ]);
            break;
            
        case 'add_skill':
            $id = $db->insert("INSERT INTO career_skills (user_id, skill_name, proficiency_level, category, created_at) VALUES (?, ?, ?, ?, NOW()) RETURNING id",
                [$userId, $data['skill_name'], $data['proficiency_level'], $data['category'] ?? '']);
            echo json_encode(['success' => true, 'id' => $id]);
            break;
            
        case 'add_project':
            $id = $db->insert("INSERT INTO portfolio_projects (user_id, project_name, description, technologies_used, project_url, created_at) VALUES (?, ?, ?, ?, ?, NOW()) RETURNING id",
                [$userId, $data['project_name'], $data['description'] ?? '', $data['technologies_used'] ?? '', $data['project_url'] ?? '']);
            echo json_encode(['success' => true, 'id' => $id]);
            break;
            
        case 'add_milestone':
            $id = $db->insert("INSERT INTO career_milestones (user_id, title, description, achievement_date, milestone_type, created_at) VALUES (?, ?, ?, ?, ?, NOW()) RETURNING id",
                [$userId, $data['title'], $data['description'] ?? '', $data['achievement_date'], $data['milestone_type'] ?? 'achievement']);
            echo json_encode(['success' => true, 'id' => $id]);
            break;
            
        case 'delete_skill':
            $db->delete("DELETE FROM career_skills WHERE id = ? AND user_id = ?", [$data['id'], $userId]);
            echo json_encode(['success' => true]);
            break;
            
        case 'delete_project':
            $db->delete("DELETE FROM portfolio_projects WHERE id = ? AND user_id = ?", [$data['id'], $userId]);
            echo json_encode(['success' => true]);
            break;
            
        case 'delete_milestone':
            $db->delete("DELETE FROM career_milestones WHERE id = ? AND user_id = ?", [$data['id'], $userId]);
            echo json_encode(['success' => true]);
            break;
            
        case 'generate_portfolio':
            $skills = $db->fetchAll("SELECT * FROM career_skills WHERE user_id = ? ORDER BY proficiency_level DESC", [$userId]) ?: [];
            $projects = $db->fetchAll("SELECT * FROM portfolio_projects WHERE user_id = ? ORDER BY created_at DESC", [$userId]) ?: [];
            $milestones = $db->fetchAll("SELECT * FROM career_milestones WHERE user_id = ? ORDER BY achievement_date DESC", [$userId]) ?: [];
            $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
            
            $portfolioHtml = generatePortfolioHTML($user, $skills, $projects, $milestones);
            
            $filename = 'portfolio_' . $userId . '_' . time() . '.html';
            $filepath = __DIR__ . '/../exports/' . $filename;
            
            if (!is_dir(__DIR__ . '/../exports/')) {
                mkdir(__DIR__ . '/../exports/', 0755, true);
            }
            
            file_put_contents($filepath, $portfolioHtml);
            
            echo json_encode([
                'success' => true,
                'portfolio_url' => '/exports/' . $filename,
                'message' => 'Portfolio website generated successfully!'
            ]);
            break;
            
        case 'generate_resume':
            $skills = $db->fetchAll("SELECT * FROM career_skills WHERE user_id = ? ORDER BY proficiency_level DESC", [$userId]) ?: [];
            $projects = $db->fetchAll("SELECT * FROM portfolio_projects WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$userId]) ?: [];
            $milestones = $db->fetchAll("SELECT * FROM career_milestones WHERE user_id = ? ORDER BY achievement_date DESC", [$userId]) ?: [];
            $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
            
            $resumeHtml = generateResumeHTML($user, $skills, $projects, $milestones);
            
            if (!is_dir(__DIR__ . '/../exports/')) {
                mkdir(__DIR__ . '/../exports/', 0755, true);
            }
            
            $format = $_GET['format'] ?? 'pdf';
            
            if ($format === 'pdf') {
                require_once __DIR__ . '/../vendor/autoload.php';
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($resumeHtml);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                
                $filename = 'resume_' . $userId . '_' . time() . '.pdf';
                $filepath = __DIR__ . '/../exports/' . $filename;
                file_put_contents($filepath, $dompdf->output());
                
                echo json_encode([
                    'success' => true,
                    'resume_url' => '/exports/' . $filename,
                    'message' => 'Resume PDF generated successfully!'
                ]);
            } else {
                $filename = 'resume_' . $userId . '_' . time() . '.html';
                $filepath = __DIR__ . '/../exports/' . $filename;
                file_put_contents($filepath, $resumeHtml);
                
                echo json_encode([
                    'success' => true,
                    'resume_url' => '/exports/' . $filename,
                    'message' => 'Resume HTML generated successfully!'
                ]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function generatePortfolioHTML($user, $skills, $projects, $milestones) {
    $name = htmlspecialchars($user['username'] ?? 'Professional');
    $email = htmlspecialchars($user['email'] ?? '');
    
    $skillsHtml = '';
    foreach ($skills as $skill) {
        $skillsHtml .= '<div class="skill-item"><h4>' . htmlspecialchars($skill['skill_name']) . '</h4><div class="skill-bar"><div class="skill-fill" style="width: ' . $skill['proficiency_level'] . '%"></div></div></div>';
    }
    
    $projectsHtml = '';
    foreach ($projects as $project) {
        $projectsHtml .= '<div class="project-card"><h3>' . htmlspecialchars($project['project_name']) . '</h3><p>' . htmlspecialchars($project['description']) . '</p>';
        if ($project['project_url']) {
            $projectsHtml .= '<a href="' . htmlspecialchars($project['project_url']) . '" target="_blank">View Project →</a>';
        }
        $projectsHtml .= '</div>';
    }
    
    return "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>$name - Portfolio</title><style>body{font-family:Arial,sans-serif;margin:0;padding:0;background:#f5f5f5}.header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;padding:60px 20px;text-align:center}.container{max-width:1200px;margin:0 auto;padding:40px 20px}.section{background:#fff;padding:30px;margin-bottom:30px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1)}h1{font-size:2.5em;margin:0}h2{color:#667eea;border-bottom:3px solid #667eea;padding-bottom:10px}.skill-item{margin-bottom:15px}h4{margin:0 0 5px 0}.skill-bar{background:#e0e0e0;height:8px;border-radius:4px}.skill-fill{background:#667eea;height:100%;border-radius:4px}.project-card{border-left:4px solid #667eea;padding-left:20px;margin-bottom:20px}.project-card h3{margin-top:0}.project-card a{color:#667eea;text-decoration:none;font-weight:bold}</style></head><body><div class='header'><h1>$name</h1><p>$email</p></div><div class='container'><div class='section'><h2>Skills</h2>$skillsHtml</div><div class='section'><h2>Projects</h2>$projectsHtml</div></div></body></html>";
}

function generateResumeHTML($user, $skills, $projects, $milestones) {
    $name = htmlspecialchars($user['username'] ?? 'Professional');
    $email = htmlspecialchars($user['email'] ?? '');
    
    $skillsList = array_map(function($s) { return htmlspecialchars($s['skill_name']); }, $skills);
    $skillsHtml = implode(', ', $skillsList);
    
    $projectsHtml = '';
    foreach ($projects as $project) {
        $projectsHtml .= '<div class="resume-item"><h4>' . htmlspecialchars($project['project_name']) . '</h4><p>' . htmlspecialchars($project['description']) . '</p></div>';
    }
    
    $milestonesHtml = '';
    foreach ($milestones as $milestone) {
        $milestonesHtml .= '<div class="resume-item"><h4>' . htmlspecialchars($milestone['title']) . '</h4><p class="date">' . date('M Y', strtotime($milestone['achievement_date'])) . '</p><p>' . htmlspecialchars($milestone['description']) . '</p></div>';
    }
    
    return "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>$name - Resume</title><style>body{font-family:'Georgia',serif;margin:0;padding:40px;background:#fff;color:#333}.header{text-align:center;border-bottom:2px solid #333;padding-bottom:20px;margin-bottom:30px}h1{font-size:2.5em;margin:0 0 10px 0}h2{color:#333;border-bottom:1px solid #ccc;padding-bottom:5px;margin-top:30px}h4{margin:10px 0 5px 0}.resume-item{margin-bottom:20px}.date{color:#666;font-style:italic}</style></head><body><div class='header'><h1>$name</h1><p>$email</p></div><h2>Skills</h2><p>$skillsHtml</p><h2>Projects</h2>$projectsHtml<h2>Achievements</h2>$milestonesHtml</body></html>";
}
