<?php
class Paginator {
    private $db;
    private $page;
    private $perPage;
    private $totalRecords;
    private $totalPages;
    
    public function __construct($page = 1, $perPage = 20) {
        $this->db = Database::getInstance();
        $this->page = max(1, (int)$page);
        $this->perPage = max(1, min(100, (int)$perPage));
    }
    
    public function paginate($table, $conditions = '', $params = [], $orderBy = 'id DESC') {
        $whereClause = !empty($conditions) ? "WHERE $conditions" : '';
        
        $countQuery = "SELECT COUNT(*) FROM $table $whereClause";
        $this->totalRecords = (int)$this->db->fetchColumn($countQuery, $params);
        $this->totalPages = ceil($this->totalRecords / $this->perPage);
        
        $offset = ($this->page - 1) * $this->perPage;
        
        $dataQuery = "SELECT * FROM $table $whereClause ORDER BY $orderBy LIMIT ? OFFSET ?";
        $dataParams = array_merge($params, [$this->perPage, $offset]);
        $data = $this->db->fetchAll($dataQuery, $dataParams);
        
        return [
            'data' => $data,
            'pagination' => $this->getPaginationInfo()
        ];
    }
    
    public function paginateQuery($query, $countQuery, $params = [], $page = null, $perPage = null) {
        if ($page !== null) $this->page = max(1, (int)$page);
        if ($perPage !== null) $this->perPage = max(1, min(100, (int)$perPage));
        
        $this->totalRecords = (int)$this->db->fetchColumn($countQuery, $params);
        $this->totalPages = ceil($this->totalRecords / $this->perPage);
        
        $offset = ($this->page - 1) * $this->perPage;
        
        $paginatedQuery = "$query LIMIT ? OFFSET ?";
        $dataParams = array_merge($params, [$this->perPage, $offset]);
        $data = $this->db->fetchAll($paginatedQuery, $dataParams);
        
        return [
            'data' => $data,
            'pagination' => $this->getPaginationInfo()
        ];
    }
    
    public function getPaginationInfo() {
        return [
            'current_page' => $this->page,
            'per_page' => $this->perPage,
            'total_records' => $this->totalRecords,
            'total_pages' => $this->totalPages,
            'has_previous' => $this->page > 1,
            'has_next' => $this->page < $this->totalPages,
            'previous_page' => $this->page > 1 ? $this->page - 1 : null,
            'next_page' => $this->page < $this->totalPages ? $this->page + 1 : null,
            'from' => $this->totalRecords > 0 ? (($this->page - 1) * $this->perPage) + 1 : 0,
            'to' => min($this->page * $this->perPage, $this->totalRecords)
        ];
    }
    
    public function renderPaginationLinks($baseUrl, $additionalParams = []) {
        if ($this->totalPages <= 1) {
            return '';
        }
        
        $html = '<div class="pagination">';
        
        if ($this->page > 1) {
            $prevUrl = $this->buildUrl($baseUrl, $this->page - 1, $additionalParams);
            $html .= '<a href="' . $prevUrl . '" class="pagination-btn">
                <i class="fas fa-chevron-left"></i> Previous
            </a>';
        } else {
            $html .= '<span class="pagination-btn disabled">
                <i class="fas fa-chevron-left"></i> Previous
            </span>';
        }
        
        $html .= '<span class="pagination-info">
            Page ' . $this->page . ' of ' . $this->totalPages . 
            ' (' . number_format($this->totalRecords) . ' total)
        </span>';
        
        if ($this->page < $this->totalPages) {
            $nextUrl = $this->buildUrl($baseUrl, $this->page + 1, $additionalParams);
            $html .= '<a href="' . $nextUrl . '" class="pagination-btn">
                Next <i class="fas fa-chevron-right"></i>
            </a>';
        } else {
            $html .= '<span class="pagination-btn disabled">
                Next <i class="fas fa-chevron-right"></i>
            </span>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    private function buildUrl($baseUrl, $page, $additionalParams = []) {
        $params = array_merge($additionalParams, ['page' => $page]);
        $queryString = http_build_query($params);
        return $baseUrl . '?' . $queryString;
    }
}
