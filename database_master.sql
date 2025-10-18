--
-- PostgreSQL database dump
--

-- Dumped from database version 16.9 (165f042)
-- Dumped by pg_dump version 17.5

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: api_tokens; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.api_tokens (
    id integer NOT NULL,
    user_id integer NOT NULL,
    token character varying(64) NOT NULL,
    name character varying(100) DEFAULT 'API Token'::character varying,
    last_used timestamp without time zone,
    expires_at timestamp without time zone NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.api_tokens OWNER TO neondb_owner;

--
-- Name: api_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.api_tokens_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.api_tokens_id_seq OWNER TO neondb_owner;

--
-- Name: api_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.api_tokens_id_seq OWNED BY public.api_tokens.id;


--
-- Name: assets; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.assets (
    id integer NOT NULL,
    user_id integer NOT NULL,
    name character varying(150) NOT NULL,
    category character varying(100),
    value numeric(12,2),
    acquisition_date date,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.assets OWNER TO neondb_owner;

--
-- Name: assets_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.assets_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.assets_id_seq OWNER TO neondb_owner;

--
-- Name: assets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.assets_id_seq OWNED BY public.assets.id;


--
-- Name: backups; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.backups (
    id integer NOT NULL,
    user_id integer,
    filename character varying(255) NOT NULL,
    backup_type character varying(50),
    file_size integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.backups OWNER TO neondb_owner;

--
-- Name: backups_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.backups_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.backups_id_seq OWNER TO neondb_owner;

--
-- Name: backups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.backups_id_seq OWNED BY public.backups.id;


--
-- Name: bills; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.bills (
    id integer NOT NULL,
    user_id integer NOT NULL,
    name character varying(150) NOT NULL,
    amount numeric(10,2) NOT NULL,
    due_date date NOT NULL,
    payment_status character varying(50) DEFAULT 'pending'::character varying,
    recurring boolean DEFAULT false,
    frequency character varying(50),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.bills OWNER TO neondb_owner;

--
-- Name: bills_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.bills_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bills_id_seq OWNER TO neondb_owner;

--
-- Name: bills_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.bills_id_seq OWNED BY public.bills.id;


--
-- Name: birthdays; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.birthdays (
    id integer NOT NULL,
    user_id integer NOT NULL,
    name character varying(150) NOT NULL,
    birth_date date NOT NULL,
    relationship character varying(100),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.birthdays OWNER TO neondb_owner;

--
-- Name: birthdays_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.birthdays_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.birthdays_id_seq OWNER TO neondb_owner;

--
-- Name: birthdays_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.birthdays_id_seq OWNED BY public.birthdays.id;


--
-- Name: crypto_alerts; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.crypto_alerts (
    id integer NOT NULL,
    user_id integer NOT NULL,
    symbol character varying(20) NOT NULL,
    crypto_symbol character varying(20),
    alert_type character varying(20) NOT NULL,
    price_target numeric(20,2) NOT NULL,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.crypto_alerts OWNER TO neondb_owner;

--
-- Name: crypto_alerts_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.crypto_alerts_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.crypto_alerts_id_seq OWNER TO neondb_owner;

--
-- Name: crypto_alerts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.crypto_alerts_id_seq OWNED BY public.crypto_alerts.id;


--
-- Name: crypto_portfolio; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.crypto_portfolio (
    id integer NOT NULL,
    user_id integer NOT NULL,
    symbol character varying(20) NOT NULL,
    name character varying(100) NOT NULL,
    crypto_id character varying(50),
    crypto_symbol character varying(20),
    crypto_name character varying(100),
    amount numeric(20,8) NOT NULL,
    purchase_price numeric(20,2) NOT NULL,
    purchase_date date NOT NULL,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.crypto_portfolio OWNER TO neondb_owner;

--
-- Name: crypto_portfolio_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.crypto_portfolio_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.crypto_portfolio_id_seq OWNER TO neondb_owner;

--
-- Name: crypto_portfolio_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.crypto_portfolio_id_seq OWNED BY public.crypto_portfolio.id;


--
-- Name: crypto_price_history; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.crypto_price_history (
    id integer NOT NULL,
    symbol character varying(20) NOT NULL,
    price numeric(20,2) NOT NULL,
    recorded_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.crypto_price_history OWNER TO neondb_owner;

--
-- Name: crypto_price_history_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.crypto_price_history_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.crypto_price_history_id_seq OWNER TO neondb_owner;

--
-- Name: crypto_price_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.crypto_price_history_id_seq OWNED BY public.crypto_price_history.id;


--
-- Name: encrypted_notes; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.encrypted_notes (
    id integer NOT NULL,
    user_id integer NOT NULL,
    title character varying(255) NOT NULL,
    encrypted_content text NOT NULL,
    encryption_iv character varying(255) NOT NULL,
    tags character varying(255),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.encrypted_notes OWNER TO neondb_owner;

--
-- Name: encrypted_notes_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.encrypted_notes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.encrypted_notes_id_seq OWNER TO neondb_owner;

--
-- Name: encrypted_notes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.encrypted_notes_id_seq OWNED BY public.encrypted_notes.id;


--
-- Name: finance; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.finance (
    id integer NOT NULL,
    user_id integer NOT NULL,
    type character varying(20) NOT NULL,
    category character varying(100),
    amount numeric(10,2) NOT NULL,
    date date NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.finance OWNER TO neondb_owner;

--
-- Name: finance_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.finance_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.finance_id_seq OWNER TO neondb_owner;

--
-- Name: finance_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.finance_id_seq OWNED BY public.finance.id;


--
-- Name: goals; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.goals (
    id integer NOT NULL,
    user_id integer NOT NULL,
    title character varying(200) NOT NULL,
    description text,
    category character varying(100),
    target_date date,
    progress integer DEFAULT 0,
    status character varying(50) DEFAULT 'active'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.goals OWNER TO neondb_owner;

--
-- Name: goals_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.goals_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.goals_id_seq OWNER TO neondb_owner;

--
-- Name: goals_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.goals_id_seq OWNED BY public.goals.id;


--
-- Name: habit_logs; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.habit_logs (
    id integer NOT NULL,
    habit_id integer NOT NULL,
    user_id integer NOT NULL,
    completed_date date NOT NULL,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.habit_logs OWNER TO neondb_owner;

--
-- Name: habit_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.habit_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.habit_logs_id_seq OWNER TO neondb_owner;

--
-- Name: habit_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.habit_logs_id_seq OWNED BY public.habit_logs.id;


--
-- Name: habits; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.habits (
    id integer NOT NULL,
    user_id integer NOT NULL,
    name character varying(150) NOT NULL,
    description text,
    frequency character varying(50),
    streak integer DEFAULT 0,
    best_streak integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.habits OWNER TO neondb_owner;

--
-- Name: habits_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.habits_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.habits_id_seq OWNER TO neondb_owner;

--
-- Name: habits_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.habits_id_seq OWNED BY public.habits.id;


--
-- Name: health; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.health (
    id integer NOT NULL,
    user_id integer NOT NULL,
    date date NOT NULL,
    weight numeric(5,2),
    exercise_minutes integer,
    water_intake numeric(5,2),
    sleep_hours numeric(4,2),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.health OWNER TO neondb_owner;

--
-- Name: health_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.health_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.health_id_seq OWNER TO neondb_owner;

--
-- Name: health_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.health_id_seq OWNED BY public.health.id;


--
-- Name: hobbies; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.hobbies (
    id integer NOT NULL,
    user_id integer NOT NULL,
    name character varying(150) NOT NULL,
    category character varying(100),
    time_spent_hours numeric(8,2) DEFAULT 0,
    progress_notes text,
    resources text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.hobbies OWNER TO neondb_owner;

--
-- Name: hobbies_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.hobbies_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.hobbies_id_seq OWNER TO neondb_owner;

--
-- Name: hobbies_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.hobbies_id_seq OWNED BY public.hobbies.id;


--
-- Name: investments; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.investments (
    id integer NOT NULL,
    user_id integer NOT NULL,
    name character varying(150) NOT NULL,
    type character varying(100),
    amount_invested numeric(12,2) NOT NULL,
    current_value numeric(12,2),
    purchase_date date,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.investments OWNER TO neondb_owner;

--
-- Name: investments_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.investments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.investments_id_seq OWNER TO neondb_owner;

--
-- Name: investments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.investments_id_seq OWNED BY public.investments.id;


--
-- Name: journal; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.journal (
    id integer NOT NULL,
    user_id integer NOT NULL,
    date date NOT NULL,
    title character varying(200),
    content text,
    mood character varying(50),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.journal OWNER TO neondb_owner;

--
-- Name: journal_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.journal_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.journal_id_seq OWNER TO neondb_owner;

--
-- Name: journal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.journal_id_seq OWNED BY public.journal.id;


--
-- Name: learning; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.learning (
    id integer NOT NULL,
    user_id integer NOT NULL,
    title character varying(200) NOT NULL,
    type character varying(50),
    platform character varying(100),
    progress integer DEFAULT 0,
    status character varying(50) DEFAULT 'in_progress'::character varying,
    start_date date,
    completion_date date,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.learning OWNER TO neondb_owner;

--
-- Name: learning_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.learning_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.learning_id_seq OWNER TO neondb_owner;

--
-- Name: learning_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.learning_id_seq OWNED BY public.learning.id;


--
-- Name: media; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.media (
    id integer NOT NULL,
    user_id integer NOT NULL,
    title character varying(200) NOT NULL,
    type character varying(50),
    status character varying(50) DEFAULT 'to_watch'::character varying,
    rating integer,
    review text,
    completion_date date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.media OWNER TO neondb_owner;

--
-- Name: media_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.media_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.media_id_seq OWNER TO neondb_owner;

--
-- Name: media_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.media_id_seq OWNED BY public.media.id;


--
-- Name: medical_records; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.medical_records (
    id integer NOT NULL,
    user_id integer NOT NULL,
    record_type character varying(100),
    title character varying(200),
    date date,
    description text,
    doctor character varying(150),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.medical_records OWNER TO neondb_owner;

--
-- Name: medical_records_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.medical_records_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.medical_records_id_seq OWNER TO neondb_owner;

--
-- Name: medical_records_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.medical_records_id_seq OWNED BY public.medical_records.id;


--
-- Name: notifications; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.notifications (
    id integer NOT NULL,
    user_id integer NOT NULL,
    type character varying(50) NOT NULL,
    title character varying(200),
    message text,
    is_read boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.notifications OWNER TO neondb_owner;

--
-- Name: notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.notifications_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.notifications_id_seq OWNER TO neondb_owner;

--
-- Name: notifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.notifications_id_seq OWNED BY public.notifications.id;


--
-- Name: subscriptions; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.subscriptions (
    id integer NOT NULL,
    user_id integer NOT NULL,
    name character varying(150) NOT NULL,
    cost numeric(10,2) NOT NULL,
    billing_cycle character varying(50),
    renewal_date date NOT NULL,
    status character varying(50) DEFAULT 'active'::character varying,
    category character varying(100),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.subscriptions OWNER TO neondb_owner;

--
-- Name: subscriptions_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.subscriptions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.subscriptions_id_seq OWNER TO neondb_owner;

--
-- Name: subscriptions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.subscriptions_id_seq OWNED BY public.subscriptions.id;


--
-- Name: tasks; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.tasks (
    id integer NOT NULL,
    user_id integer NOT NULL,
    title character varying(200) NOT NULL,
    description text,
    category character varying(100),
    priority character varying(50) DEFAULT 'medium'::character varying,
    due_date date,
    status character varying(50) DEFAULT 'pending'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    recurrence_type character varying(20),
    recurrence_interval integer DEFAULT 1,
    parent_task_id integer,
    depends_on_task_id integer,
    pomodoro_sessions integer DEFAULT 0,
    estimated_pomodoros integer DEFAULT 1
);


ALTER TABLE public.tasks OWNER TO neondb_owner;

--
-- Name: tasks_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.tasks_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tasks_id_seq OWNER TO neondb_owner;

--
-- Name: tasks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.tasks_id_seq OWNED BY public.tasks.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.users (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    email character varying(150) NOT NULL,
    password character varying(255) NOT NULL,
    telegram_chat_id character varying(50),
    settings text,
    is_admin boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    totp_secret character varying(32),
    totp_enabled boolean DEFAULT false,
    backup_codes text
);


ALTER TABLE public.users OWNER TO neondb_owner;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO neondb_owner;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: api_tokens id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.api_tokens ALTER COLUMN id SET DEFAULT nextval('public.api_tokens_id_seq'::regclass);


--
-- Name: assets id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.assets ALTER COLUMN id SET DEFAULT nextval('public.assets_id_seq'::regclass);


--
-- Name: backups id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.backups ALTER COLUMN id SET DEFAULT nextval('public.backups_id_seq'::regclass);


--
-- Name: bills id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.bills ALTER COLUMN id SET DEFAULT nextval('public.bills_id_seq'::regclass);


--
-- Name: birthdays id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.birthdays ALTER COLUMN id SET DEFAULT nextval('public.birthdays_id_seq'::regclass);


--
-- Name: crypto_alerts id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.crypto_alerts ALTER COLUMN id SET DEFAULT nextval('public.crypto_alerts_id_seq'::regclass);


--
-- Name: crypto_portfolio id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.crypto_portfolio ALTER COLUMN id SET DEFAULT nextval('public.crypto_portfolio_id_seq'::regclass);


--
-- Name: crypto_price_history id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.crypto_price_history ALTER COLUMN id SET DEFAULT nextval('public.crypto_price_history_id_seq'::regclass);


--
-- Name: encrypted_notes id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.encrypted_notes ALTER COLUMN id SET DEFAULT nextval('public.encrypted_notes_id_seq'::regclass);


--
-- Name: finance id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.finance ALTER COLUMN id SET DEFAULT nextval('public.finance_id_seq'::regclass);


--
-- Name: goals id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.goals ALTER COLUMN id SET DEFAULT nextval('public.goals_id_seq'::regclass);


--
-- Name: habit_logs id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.habit_logs ALTER COLUMN id SET DEFAULT nextval('public.habit_logs_id_seq'::regclass);


--
-- Name: habits id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.habits ALTER COLUMN id SET DEFAULT nextval('public.habits_id_seq'::regclass);


--
-- Name: health id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.health ALTER COLUMN id SET DEFAULT nextval('public.health_id_seq'::regclass);


--
-- Name: hobbies id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.hobbies ALTER COLUMN id SET DEFAULT nextval('public.hobbies_id_seq'::regclass);


--
-- Name: investments id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.investments ALTER COLUMN id SET DEFAULT nextval('public.investments_id_seq'::regclass);


--
-- Name: journal id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.journal ALTER COLUMN id SET DEFAULT nextval('public.journal_id_seq'::regclass);


--
-- Name: learning id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.learning ALTER COLUMN id SET DEFAULT nextval('public.learning_id_seq'::regclass);


--
-- Name: media id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.media ALTER COLUMN id SET DEFAULT nextval('public.media_id_seq'::regclass);


--
-- Name: medical_records id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.medical_records ALTER COLUMN id SET DEFAULT nextval('public.medical_records_id_seq'::regclass);


--
-- Name: notifications id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.notifications ALTER COLUMN id SET DEFAULT nextval('public.notifications_id_seq'::regclass);


--
-- Name: subscriptions id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.subscriptions ALTER COLUMN id SET DEFAULT nextval('public.subscriptions_id_seq'::regclass);


--
-- Name: tasks id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.tasks ALTER COLUMN id SET DEFAULT nextval('public.tasks_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: api_tokens api_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.api_tokens
    ADD CONSTRAINT api_tokens_pkey PRIMARY KEY (id);


--
-- Name: api_tokens api_tokens_token_key; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.api_tokens
    ADD CONSTRAINT api_tokens_token_key UNIQUE (token);


--
-- Name: assets assets_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.assets
    ADD CONSTRAINT assets_pkey PRIMARY KEY (id);


--
-- Name: backups backups_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.backups
    ADD CONSTRAINT backups_pkey PRIMARY KEY (id);


--
-- Name: bills bills_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.bills
    ADD CONSTRAINT bills_pkey PRIMARY KEY (id);


--
-- Name: birthdays birthdays_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.birthdays
    ADD CONSTRAINT birthdays_pkey PRIMARY KEY (id);


--
-- Name: crypto_alerts crypto_alerts_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.crypto_alerts
    ADD CONSTRAINT crypto_alerts_pkey PRIMARY KEY (id);


--
-- Name: crypto_portfolio crypto_portfolio_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.crypto_portfolio
    ADD CONSTRAINT crypto_portfolio_pkey PRIMARY KEY (id);


--
-- Name: crypto_price_history crypto_price_history_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.crypto_price_history
    ADD CONSTRAINT crypto_price_history_pkey PRIMARY KEY (id);


--
-- Name: encrypted_notes encrypted_notes_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.encrypted_notes
    ADD CONSTRAINT encrypted_notes_pkey PRIMARY KEY (id);


--
-- Name: finance finance_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.finance
    ADD CONSTRAINT finance_pkey PRIMARY KEY (id);


--
-- Name: goals goals_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.goals
    ADD CONSTRAINT goals_pkey PRIMARY KEY (id);


--
-- Name: habit_logs habit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.habit_logs
    ADD CONSTRAINT habit_logs_pkey PRIMARY KEY (id);


--
-- Name: habits habits_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.habits
    ADD CONSTRAINT habits_pkey PRIMARY KEY (id);


--
-- Name: health health_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.health
    ADD CONSTRAINT health_pkey PRIMARY KEY (id);


--
-- Name: hobbies hobbies_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.hobbies
    ADD CONSTRAINT hobbies_pkey PRIMARY KEY (id);


--
-- Name: investments investments_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.investments
    ADD CONSTRAINT investments_pkey PRIMARY KEY (id);


--
-- Name: journal journal_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.journal
    ADD CONSTRAINT journal_pkey PRIMARY KEY (id);


--
-- Name: learning learning_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.learning
    ADD CONSTRAINT learning_pkey PRIMARY KEY (id);


--
-- Name: media media_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.media
    ADD CONSTRAINT media_pkey PRIMARY KEY (id);


--
-- Name: medical_records medical_records_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.medical_records
    ADD CONSTRAINT medical_records_pkey PRIMARY KEY (id);


--
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- Name: subscriptions subscriptions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_pkey PRIMARY KEY (id);


--
-- Name: tasks tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_pkey PRIMARY KEY (id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: idx_api_tokens_token; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_api_tokens_token ON public.api_tokens USING btree (token);


--
-- Name: idx_api_tokens_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_api_tokens_user ON public.api_tokens USING btree (user_id);


--
-- Name: idx_assets_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_assets_user ON public.assets USING btree (user_id);


--
-- Name: idx_bills_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_bills_user ON public.bills USING btree (user_id);


--
-- Name: idx_birthdays_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_birthdays_user ON public.birthdays USING btree (user_id);


--
-- Name: idx_crypto_alerts_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_crypto_alerts_user ON public.crypto_alerts USING btree (user_id);


--
-- Name: idx_crypto_portfolio_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_crypto_portfolio_user ON public.crypto_portfolio USING btree (user_id);


--
-- Name: idx_crypto_price_date; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_crypto_price_date ON public.crypto_price_history USING btree (recorded_at);


--
-- Name: idx_crypto_price_symbol; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_crypto_price_symbol ON public.crypto_price_history USING btree (symbol);


--
-- Name: idx_encrypted_notes_created; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_encrypted_notes_created ON public.encrypted_notes USING btree (created_at);


--
-- Name: idx_encrypted_notes_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_encrypted_notes_user ON public.encrypted_notes USING btree (user_id);


--
-- Name: idx_finance_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_finance_user ON public.finance USING btree (user_id);


--
-- Name: idx_goals_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_goals_user ON public.goals USING btree (user_id);


--
-- Name: idx_habits_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_habits_user ON public.habits USING btree (user_id);


--
-- Name: idx_health_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_health_user ON public.health USING btree (user_id);


--
-- Name: idx_hobbies_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_hobbies_user ON public.hobbies USING btree (user_id);


--
-- Name: idx_investments_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_investments_user ON public.investments USING btree (user_id);


--
-- Name: idx_journal_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_journal_user ON public.journal USING btree (user_id);


--
-- Name: idx_learning_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_learning_user ON public.learning USING btree (user_id);


--
-- Name: idx_media_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_media_user ON public.media USING btree (user_id);


--
-- Name: idx_notifications_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_notifications_user ON public.notifications USING btree (user_id);


--
-- Name: idx_subscriptions_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_subscriptions_user ON public.subscriptions USING btree (user_id);


--
-- Name: idx_tasks_depends; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_tasks_depends ON public.tasks USING btree (depends_on_task_id);


--
-- Name: idx_tasks_parent; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_tasks_parent ON public.tasks USING btree (parent_task_id);


--
-- Name: idx_tasks_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_tasks_user ON public.tasks USING btree (user_id);


--
-- Name: api_tokens api_tokens_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.api_tokens
    ADD CONSTRAINT api_tokens_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: assets assets_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.assets
    ADD CONSTRAINT assets_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: backups backups_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.backups
    ADD CONSTRAINT backups_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: bills bills_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.bills
    ADD CONSTRAINT bills_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: birthdays birthdays_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.birthdays
    ADD CONSTRAINT birthdays_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: crypto_alerts crypto_alerts_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.crypto_alerts
    ADD CONSTRAINT crypto_alerts_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: crypto_portfolio crypto_portfolio_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.crypto_portfolio
    ADD CONSTRAINT crypto_portfolio_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: encrypted_notes encrypted_notes_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.encrypted_notes
    ADD CONSTRAINT encrypted_notes_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: finance finance_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.finance
    ADD CONSTRAINT finance_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: goals goals_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.goals
    ADD CONSTRAINT goals_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: habit_logs habit_logs_habit_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.habit_logs
    ADD CONSTRAINT habit_logs_habit_id_fkey FOREIGN KEY (habit_id) REFERENCES public.habits(id) ON DELETE CASCADE;


--
-- Name: habit_logs habit_logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.habit_logs
    ADD CONSTRAINT habit_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: habits habits_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.habits
    ADD CONSTRAINT habits_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: health health_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.health
    ADD CONSTRAINT health_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: hobbies hobbies_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.hobbies
    ADD CONSTRAINT hobbies_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: investments investments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.investments
    ADD CONSTRAINT investments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: journal journal_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.journal
    ADD CONSTRAINT journal_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: learning learning_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.learning
    ADD CONSTRAINT learning_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: media media_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.media
    ADD CONSTRAINT media_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: medical_records medical_records_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.medical_records
    ADD CONSTRAINT medical_records_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: notifications notifications_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: subscriptions subscriptions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: tasks tasks_depends_on_task_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_depends_on_task_id_fkey FOREIGN KEY (depends_on_task_id) REFERENCES public.tasks(id) ON DELETE SET NULL;


--
-- Name: tasks tasks_parent_task_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_parent_task_id_fkey FOREIGN KEY (parent_task_id) REFERENCES public.tasks(id) ON DELETE CASCADE;


--
-- Name: tasks tasks_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: DEFAULT PRIVILEGES FOR SEQUENCES; Type: DEFAULT ACL; Schema: public; Owner: cloud_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE cloud_admin IN SCHEMA public GRANT ALL ON SEQUENCES TO neon_superuser WITH GRANT OPTION;


--
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: public; Owner: cloud_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE cloud_admin IN SCHEMA public GRANT SELECT,INSERT,REFERENCES,DELETE,TRIGGER,TRUNCATE,UPDATE ON TABLES TO neon_superuser WITH GRANT OPTION;


--
-- PostgreSQL database dump complete
--

-- New Features Database Schema
-- Life Atlas Organizer - Additional Modules

-- 1. CALENDAR MODULE
CREATE TABLE IF NOT EXISTS calendar_events (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    start_datetime TIMESTAMP NOT NULL,
    end_datetime TIMESTAMP NOT NULL,
    event_type VARCHAR(50), -- 'custom', 'task', 'bill', 'birthday', 'gym', 'appointment'
    source_module VARCHAR(50), -- which module the event came from
    source_id INTEGER, -- ID from the source module
    location VARCHAR(255),
    reminder_minutes INTEGER DEFAULT 30,
    color VARCHAR(20),
    is_all_day BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_calendar_events_user_date ON calendar_events(user_id, start_datetime);
CREATE INDEX idx_calendar_events_source ON calendar_events(source_module, source_id);

-- 2. NOTES / KNOWLEDGE BASE MODULE
CREATE TABLE IF NOT EXISTS notes (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(200) NOT NULL,
    content TEXT,
    category VARCHAR(100),
    tags VARCHAR(500), -- JSON array of tags
    is_favorite BOOLEAN DEFAULT FALSE,
    is_archived BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS note_categories (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_notes_user ON notes(user_id);
CREATE INDEX idx_notes_category ON notes(category);

-- 3. PROJECT MANAGEMENT (KANBAN) MODULE
CREATE TABLE IF NOT EXISTS projects (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'active', -- 'active', 'completed', 'archived'
    priority VARCHAR(50) DEFAULT 'medium',
    start_date DATE,
    target_date DATE,
    progress INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS project_tasks (
    id SERIAL PRIMARY KEY,
    project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'todo', -- 'todo', 'in_progress', 'done'
    priority VARCHAR(50) DEFAULT 'medium',
    assigned_to INTEGER REFERENCES users(id),
    due_date DATE,
    position INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS project_checklists (
    id SERIAL PRIMARY KEY,
    task_id INTEGER NOT NULL REFERENCES project_tasks(id) ON DELETE CASCADE,
    item_text VARCHAR(300) NOT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    position INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS project_attachments (
    id SERIAL PRIMARY KEY,
    project_id INTEGER REFERENCES projects(id) ON DELETE CASCADE,
    task_id INTEGER REFERENCES project_tasks(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INTEGER,
    file_type VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_projects_user ON projects(user_id);
CREATE INDEX idx_project_tasks_project ON project_tasks(project_id);

-- 4. POMODORO TIMER MODULE
CREATE TABLE IF NOT EXISTS pomodoro_sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    task_id INTEGER REFERENCES tasks(id) ON DELETE SET NULL,
    habit_id INTEGER REFERENCES habits(id) ON DELETE SET NULL,
    project_task_id INTEGER REFERENCES project_tasks(id) ON DELETE SET NULL,
    session_type VARCHAR(20) DEFAULT 'work', -- 'work', 'short_break', 'long_break'
    duration_minutes INTEGER NOT NULL,
    completed BOOLEAN DEFAULT FALSE,
    start_time TIMESTAMP,
    end_time TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_pomodoro_user_date ON pomodoro_sessions(user_id, created_at);

-- 5. DEDICATED BUDGETING MODULE (ENVELOPE BUDGETING)
CREATE TABLE IF NOT EXISTS budget_envelopes (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100),
    monthly_allocation DECIMAL(10,2) NOT NULL DEFAULT 0,
    current_balance DECIMAL(10,2) NOT NULL DEFAULT 0,
    color VARCHAR(20),
    icon VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS budget_transactions (
    id SERIAL PRIMARY KEY,
    envelope_id INTEGER NOT NULL REFERENCES budget_envelopes(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    amount DECIMAL(10,2) NOT NULL,
    transaction_type VARCHAR(20) NOT NULL, -- 'debit', 'credit', 'allocation'
    description TEXT,
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_budget_envelopes_user ON budget_envelopes(user_id);
CREATE INDEX idx_budget_transactions_envelope ON budget_transactions(envelope_id);

-- 6. DEBT PAYOFF PLANNER MODULE
CREATE TABLE IF NOT EXISTS debts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(150) NOT NULL,
    debt_type VARCHAR(50), -- 'credit_card', 'student_loan', 'mortgage', 'personal_loan', 'car_loan'
    principal_amount DECIMAL(12,2) NOT NULL,
    current_balance DECIMAL(12,2) NOT NULL,
    interest_rate DECIMAL(5,2) NOT NULL,
    minimum_payment DECIMAL(10,2),
    payment_due_day INTEGER, -- day of month
    start_date DATE,
    payoff_strategy VARCHAR(50), -- 'snowball', 'avalanche', 'custom'
    priority_order INTEGER,
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS debt_payments (
    id SERIAL PRIMARY KEY,
    debt_id INTEGER NOT NULL REFERENCES debts(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    payment_amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    principal_paid DECIMAL(10,2),
    interest_paid DECIMAL(10,2),
    remaining_balance DECIMAL(12,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_debts_user ON debts(user_id);
CREATE INDEX idx_debt_payments_debt ON debt_payments(debt_id);

-- 7. RECIPE BOOK & MEAL PLANNER MODULE
CREATE TABLE IF NOT EXISTS recipes (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    category VARCHAR(100), -- 'breakfast', 'lunch', 'dinner', 'snack', 'dessert'
    cuisine VARCHAR(100),
    prep_time INTEGER, -- minutes
    cook_time INTEGER, -- minutes
    servings INTEGER,
    ingredients TEXT, -- JSON array
    instructions TEXT,
    image_url VARCHAR(500),
    source_url VARCHAR(500),
    nutrition_info TEXT, -- JSON object
    is_favorite BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS meal_plans (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    recipe_id INTEGER REFERENCES recipes(id) ON DELETE CASCADE,
    meal_date DATE NOT NULL,
    meal_type VARCHAR(50), -- 'breakfast', 'lunch', 'dinner', 'snack'
    servings INTEGER DEFAULT 1,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS shopping_lists (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(150) NOT NULL,
    week_start_date DATE,
    status VARCHAR(50) DEFAULT 'active', -- 'active', 'completed'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS shopping_list_items (
    id SERIAL PRIMARY KEY,
    shopping_list_id INTEGER NOT NULL REFERENCES shopping_lists(id) ON DELETE CASCADE,
    item_name VARCHAR(200) NOT NULL,
    quantity VARCHAR(50),
    category VARCHAR(100),
    is_purchased BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_recipes_user ON recipes(user_id);
CREATE INDEX idx_meal_plans_user_date ON meal_plans(user_id, meal_date);

-- 8. VEHICLE MAINTENANCE MODULE
CREATE TABLE IF NOT EXISTS vehicles (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    make VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    year INTEGER,
    vin VARCHAR(50),
    license_plate VARCHAR(50),
    current_mileage INTEGER,
    purchase_date DATE,
    color VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS vehicle_maintenance (
    id SERIAL PRIMARY KEY,
    vehicle_id INTEGER NOT NULL REFERENCES vehicles(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    service_type VARCHAR(100) NOT NULL, -- 'oil_change', 'tire_rotation', 'brake_service', 'inspection', etc.
    service_date DATE NOT NULL,
    mileage INTEGER,
    cost DECIMAL(10,2),
    service_provider VARCHAR(150),
    next_service_date DATE,
    next_service_mileage INTEGER,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_vehicles_user ON vehicles(user_id);
CREATE INDEX idx_vehicle_maintenance_vehicle ON vehicle_maintenance(vehicle_id);

-- 9. SLEEP TRACKER MODULE
CREATE TABLE IF NOT EXISTS sleep_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    sleep_date DATE NOT NULL,
    bedtime TIMESTAMP,
    wake_time TIMESTAMP,
    duration_hours DECIMAL(4,2),
    quality VARCHAR(50), -- 'poor', 'fair', 'good', 'excellent'
    sleep_score INTEGER, -- 1-100
    notes TEXT,
    mood_on_waking VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, sleep_date)
);

CREATE INDEX idx_sleep_logs_user_date ON sleep_logs(user_id, sleep_date);

-- 10. MEDICATION & SUPPLEMENT TRACKER MODULE
CREATE TABLE IF NOT EXISTS medications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    medication_type VARCHAR(50), -- 'medication', 'supplement', 'vitamin'
    dosage VARCHAR(100),
    frequency VARCHAR(100), -- 'daily', 'twice_daily', 'weekly', etc.
    time_of_day VARCHAR(100), -- 'morning', 'afternoon', 'evening', 'bedtime'
    start_date DATE,
    end_date DATE,
    prescribing_doctor VARCHAR(150),
    purpose TEXT,
    current_quantity INTEGER,
    refill_reminder_quantity INTEGER,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS medication_logs (
    id SERIAL PRIMARY KEY,
    medication_id INTEGER NOT NULL REFERENCES medications(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    log_date DATE NOT NULL,
    log_time TIME,
    taken BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_medications_user ON medications(user_id);
CREATE INDEX idx_medication_logs_user_date ON medication_logs(user_id, log_date);

-- 11. SYMPTOM TRACKER MODULE
CREATE TABLE IF NOT EXISTS symptoms (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100), -- 'pain', 'digestive', 'respiratory', 'mental', etc.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS symptom_logs (
    id SERIAL PRIMARY KEY,
    symptom_id INTEGER NOT NULL REFERENCES symptoms(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    log_date DATE NOT NULL,
    log_time TIME,
    severity INTEGER, -- 1-10 scale
    duration_minutes INTEGER,
    triggers TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_symptoms_user ON symptoms(user_id);
CREATE INDEX idx_symptom_logs_user_date ON symptom_logs(user_id, log_date);

-- 12. PERSONAL CRM (CONTACTS) MODULE
CREATE TABLE IF NOT EXISTS contacts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    relationship VARCHAR(100), -- 'family', 'friend', 'professional', 'acquaintance'
    email VARCHAR(200),
    phone VARCHAR(50),
    birthday DATE,
    address TEXT,
    company VARCHAR(150),
    job_title VARCHAR(150),
    notes TEXT,
    importance VARCHAR(50) DEFAULT 'medium', -- 'low', 'medium', 'high', 'vip'
    tags VARCHAR(500), -- JSON array
    is_favorite BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contact_interactions (
    id SERIAL PRIMARY KEY,
    contact_id INTEGER NOT NULL REFERENCES contacts(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    interaction_date DATE NOT NULL,
    interaction_type VARCHAR(100), -- 'call', 'email', 'meeting', 'text', 'social'
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contact_reminders (
    id SERIAL PRIMARY KEY,
    contact_id INTEGER NOT NULL REFERENCES contacts(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    reminder_type VARCHAR(50), -- 'keep_in_touch', 'birthday', 'anniversary', 'follow_up'
    frequency_days INTEGER, -- how often to remind (e.g., every 7 days)
    last_contact_date DATE,
    next_reminder_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_contacts_user ON contacts(user_id);
CREATE INDEX idx_contact_interactions_contact ON contact_interactions(contact_id);

-- 13. EVENT PLANNER MODULE
CREATE TABLE IF NOT EXISTS events (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    event_type VARCHAR(100), -- 'party', 'wedding', 'meeting', 'trip', 'other'
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME,
    location VARCHAR(300),
    budget DECIMAL(10,2),
    actual_cost DECIMAL(10,2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'planning', -- 'planning', 'confirmed', 'completed', 'cancelled'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS event_checklists (
    id SERIAL PRIMARY KEY,
    event_id INTEGER NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    item_text VARCHAR(300) NOT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    due_date DATE,
    position INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS event_guests (
    id SERIAL PRIMARY KEY,
    event_id INTEGER NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    contact_id INTEGER REFERENCES contacts(id) ON DELETE SET NULL,
    name VARCHAR(200) NOT NULL,
    email VARCHAR(200),
    phone VARCHAR(50),
    rsvp_status VARCHAR(50) DEFAULT 'pending', -- 'pending', 'attending', 'declined', 'maybe'
    plus_one INTEGER DEFAULT 0,
    dietary_restrictions TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS event_budget_items (
    id SERIAL PRIMARY KEY,
    event_id INTEGER NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    category VARCHAR(100) NOT NULL,
    description VARCHAR(300),
    estimated_cost DECIMAL(10,2),
    actual_cost DECIMAL(10,2),
    is_paid BOOLEAN DEFAULT FALSE,
    payment_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_events_user ON events(user_id);
CREATE INDEX idx_event_guests_event ON event_guests(event_id);

-- 14. MULTI-USER / FAMILY SHARING MODULE
CREATE TABLE IF NOT EXISTS shared_modules (
    id SERIAL PRIMARY KEY,
    owner_user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    module_name VARCHAR(100) NOT NULL, -- 'calendar', 'shopping_lists', 'bills', 'meal_plans', etc.
    share_name VARCHAR(200), -- friendly name for this share (e.g., "Family Calendar")
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS shared_access (
    id SERIAL PRIMARY KEY,
    shared_module_id INTEGER NOT NULL REFERENCES shared_modules(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    permission_level VARCHAR(50) DEFAULT 'view', -- 'view', 'edit', 'admin'
    invited_email VARCHAR(200),
    invitation_status VARCHAR(50) DEFAULT 'pending', -- 'pending', 'accepted', 'declined'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    accepted_at TIMESTAMP
);

CREATE INDEX idx_shared_modules_owner ON shared_modules(owner_user_id);
CREATE INDEX idx_shared_access_user ON shared_access(user_id);

-- 15. CALENDAR SYNC (GOOGLE/OUTLOOK) MODULE
CREATE TABLE IF NOT EXISTS calendar_sync_settings (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    provider VARCHAR(50) NOT NULL, -- 'google', 'outlook', 'caldav'
    calendar_name VARCHAR(200),
    sync_enabled BOOLEAN DEFAULT TRUE,
    sync_direction VARCHAR(50) DEFAULT 'bidirectional', -- 'import_only', 'export_only', 'bidirectional'
    last_sync_at TIMESTAMP,
    access_token TEXT,
    refresh_token TEXT,
    token_expires_at TIMESTAMP,
    calendar_id VARCHAR(200), -- provider-specific calendar ID
    settings TEXT, -- JSON object for provider-specific settings
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, provider, calendar_id)
);

CREATE TABLE IF NOT EXISTS calendar_sync_logs (
    id SERIAL PRIMARY KEY,
    sync_setting_id INTEGER NOT NULL REFERENCES calendar_sync_settings(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    sync_direction VARCHAR(50), -- 'import', 'export'
    events_synced INTEGER DEFAULT 0,
    status VARCHAR(50), -- 'success', 'partial', 'failed'
    error_message TEXT,
    sync_started_at TIMESTAMP,
    sync_completed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_calendar_sync_settings_user ON calendar_sync_settings(user_id);
CREATE INDEX idx_calendar_sync_logs_setting ON calendar_sync_logs(sync_setting_id);
-- Life Atlas Organizer v2 Database Migration
-- New Modules: Career, Learning, Family, Travel, Wellness, Finance Advanced, AI Life Map

-- ========================================
-- WORK & CAREER CENTER MODULE
-- ========================================

-- Job Applications Tracker
CREATE TABLE job_applications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    company_name VARCHAR(200) NOT NULL,
    position VARCHAR(200) NOT NULL,
    job_url TEXT,
    status VARCHAR(50) DEFAULT 'applied',
    application_date DATE NOT NULL,
    follow_up_date DATE,
    salary_range VARCHAR(100),
    location VARCHAR(200),
    job_type VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_job_applications_user ON job_applications(user_id);
CREATE INDEX idx_job_applications_status ON job_applications(status);

-- Interview Tracker
CREATE TABLE interviews (
    id SERIAL PRIMARY KEY,
    job_application_id INTEGER REFERENCES job_applications(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    interview_type VARCHAR(100),
    interview_date TIMESTAMP NOT NULL,
    interviewer_name VARCHAR(200),
    interview_notes TEXT,
    outcome VARCHAR(50),
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_interviews_user ON interviews(user_id);
CREATE INDEX idx_interviews_job ON interviews(job_application_id);

-- Career Certifications & Skills
CREATE TABLE career_certifications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    issuing_organization VARCHAR(200),
    issue_date DATE,
    expiry_date DATE,
    credential_id VARCHAR(100),
    credential_url TEXT,
    skills_gained TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_certifications_user ON career_certifications(user_id);

-- Resume Versions
CREATE TABLE resume_versions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    version_name VARCHAR(200) NOT NULL,
    file_path TEXT,
    content TEXT,
    ai_feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- LEARNING & KNOWLEDGE HUB MODULE
-- ========================================

-- Courses Tracker
CREATE TABLE courses (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(250) NOT NULL,
    platform VARCHAR(100),
    instructor VARCHAR(200),
    course_url TEXT,
    status VARCHAR(50) DEFAULT 'not_started',
    progress INTEGER DEFAULT 0,
    start_date DATE,
    completion_date DATE,
    certificate_url TEXT,
    rating INTEGER,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_courses_user ON courses(user_id);
CREATE INDEX idx_courses_status ON courses(status);

-- Learning Flashcards
CREATE TABLE flashcards (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    course_id INTEGER REFERENCES courses(id) ON DELETE CASCADE,
    front_text TEXT NOT NULL,
    back_text TEXT NOT NULL,
    category VARCHAR(100),
    difficulty VARCHAR(20),
    last_reviewed TIMESTAMP,
    review_count INTEGER DEFAULT 0,
    mastery_level INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_flashcards_user ON flashcards(user_id);

-- Books Tracker
CREATE TABLE books (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(250) NOT NULL,
    author VARCHAR(200),
    isbn VARCHAR(50),
    status VARCHAR(50) DEFAULT 'to_read',
    current_page INTEGER DEFAULT 0,
    total_pages INTEGER,
    rating INTEGER,
    notes TEXT,
    started_date DATE,
    completed_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_books_user ON books(user_id);
CREATE INDEX idx_books_status ON books(status);

-- AI Document Summaries
CREATE TABLE document_summaries (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(250) NOT NULL,
    original_content TEXT,
    ai_summary TEXT,
    key_points TEXT,
    document_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- HOUSEHOLD & FAMILY MANAGER MODULE
-- ========================================

-- Family Members
CREATE TABLE family_members (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    relationship VARCHAR(100),
    email VARCHAR(200),
    phone VARCHAR(50),
    birthday DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_family_members_user ON family_members(user_id);

-- Shared Household Tasks
CREATE TABLE household_tasks (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    assigned_to_member_id INTEGER REFERENCES family_members(id) ON DELETE SET NULL,
    title VARCHAR(250) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    priority VARCHAR(50) DEFAULT 'medium',
    due_date DATE,
    completed BOOLEAN DEFAULT FALSE,
    recurring BOOLEAN DEFAULT FALSE,
    frequency VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_household_tasks_user ON household_tasks(user_id);

-- Household Expense Split
CREATE TABLE household_expenses (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    description VARCHAR(250) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    paid_by_member_id INTEGER REFERENCES family_members(id) ON DELETE SET NULL,
    expense_date DATE NOT NULL,
    category VARCHAR(100),
    split_type VARCHAR(50) DEFAULT 'equal',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Household Expense Shares
CREATE TABLE household_expense_shares (
    id SERIAL PRIMARY KEY,
    household_expense_id INTEGER REFERENCES household_expenses(id) ON DELETE CASCADE,
    family_member_id INTEGER REFERENCES family_members(id) ON DELETE CASCADE,
    share_amount DECIMAL(10,2) NOT NULL,
    paid BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Grocery Planner
CREATE TABLE grocery_lists (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE grocery_items (
    id SERIAL PRIMARY KEY,
    grocery_list_id INTEGER REFERENCES grocery_lists(id) ON DELETE CASCADE,
    item_name VARCHAR(200) NOT NULL,
    quantity VARCHAR(50),
    category VARCHAR(100),
    purchased BOOLEAN DEFAULT FALSE,
    price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- TRAVEL PLANNER & JOURNAL MODULE
-- ========================================

-- Trips
CREATE TABLE trips (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    destination VARCHAR(250) NOT NULL,
    country VARCHAR(100),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    budget DECIMAL(12,2),
    actual_spent DECIMAL(12,2),
    trip_type VARCHAR(50),
    status VARCHAR(50) DEFAULT 'planned',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_trips_user ON trips(user_id);
CREATE INDEX idx_trips_dates ON trips(start_date, end_date);

-- Trip Itinerary
CREATE TABLE trip_itinerary (
    id SERIAL PRIMARY KEY,
    trip_id INTEGER REFERENCES trips(id) ON DELETE CASCADE,
    day_number INTEGER NOT NULL,
    date DATE NOT NULL,
    title VARCHAR(250),
    description TEXT,
    location VARCHAR(250),
    start_time TIME,
    end_time TIME,
    cost DECIMAL(10,2),
    booking_reference VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_itinerary_trip ON trip_itinerary(trip_id);

-- Packing Lists
CREATE TABLE packing_lists (
    id SERIAL PRIMARY KEY,
    trip_id INTEGER REFERENCES trips(id) ON DELETE CASCADE,
    item_name VARCHAR(200) NOT NULL,
    category VARCHAR(100),
    packed BOOLEAN DEFAULT FALSE,
    quantity INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Travel Journal Entries
CREATE TABLE travel_journal (
    id SERIAL PRIMARY KEY,
    trip_id INTEGER REFERENCES trips(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    entry_date DATE NOT NULL,
    title VARCHAR(250),
    content TEXT NOT NULL,
    location VARCHAR(250),
    mood VARCHAR(50),
    weather VARCHAR(50),
    photos_urls TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- WELLNESS & MINDFULNESS HUB MODULE
-- ========================================

-- Meditation Sessions
CREATE TABLE meditation_sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    session_date DATE NOT NULL,
    duration_minutes INTEGER NOT NULL,
    meditation_type VARCHAR(100),
    technique VARCHAR(100),
    mood_before VARCHAR(50),
    mood_after VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_meditation_user ON meditation_sessions(user_id);
CREATE INDEX idx_meditation_date ON meditation_sessions(session_date);

-- Breathing Exercises
CREATE TABLE breathing_exercises (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    exercise_date DATE NOT NULL,
    exercise_type VARCHAR(100),
    duration_minutes INTEGER NOT NULL,
    rounds_completed INTEGER,
    stress_level_before INTEGER,
    stress_level_after INTEGER,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_breathing_user ON breathing_exercises(user_id);

-- Sleep Tracking
CREATE TABLE sleep_tracking (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    sleep_date DATE NOT NULL,
    bedtime TIME,
    wake_time TIME,
    duration_hours DECIMAL(4,2),
    quality_rating INTEGER,
    sleep_notes TEXT,
    dreams TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_sleep_user ON sleep_tracking(user_id);
CREATE INDEX idx_sleep_date ON sleep_tracking(sleep_date);

-- Stress & Wellness Logs
CREATE TABLE stress_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    log_date DATE NOT NULL,
    stress_level INTEGER NOT NULL,
    triggers TEXT,
    coping_strategies TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- FINANCE ADVANCED MODULE
-- ========================================

-- Subscription Manager (Enhanced)
CREATE TABLE subscriptions_advanced (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    service_name VARCHAR(200) NOT NULL,
    category VARCHAR(100),
    amount DECIMAL(10,2) NOT NULL,
    billing_cycle VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    next_billing_date DATE NOT NULL,
    auto_renew BOOLEAN DEFAULT TRUE,
    payment_method VARCHAR(100),
    subscription_url TEXT,
    cancellation_reminder_days INTEGER DEFAULT 7,
    status VARCHAR(50) DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_subscriptions_advanced_user ON subscriptions_advanced(user_id);

-- Investment Portfolio (Enhanced)
CREATE TABLE investment_portfolio (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    asset_name VARCHAR(200) NOT NULL,
    asset_type VARCHAR(50) NOT NULL,
    symbol VARCHAR(20),
    quantity DECIMAL(20,8) NOT NULL,
    purchase_price DECIMAL(20,2) NOT NULL,
    current_price DECIMAL(20,2),
    purchase_date DATE NOT NULL,
    broker VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_investment_user ON investment_portfolio(user_id);

-- Tax Documents & Receipts
CREATE TABLE tax_documents (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    document_name VARCHAR(250) NOT NULL,
    tax_year INTEGER NOT NULL,
    category VARCHAR(100),
    amount DECIMAL(12,2),
    file_path TEXT,
    document_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_tax_documents_user ON tax_documents(user_id);
CREATE INDEX idx_tax_documents_year ON tax_documents(tax_year);

-- Financial Goals & Projections
CREATE TABLE financial_projections (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    projection_type VARCHAR(100) NOT NULL,
    target_amount DECIMAL(12,2) NOT NULL,
    current_amount DECIMAL(12,2),
    target_date DATE NOT NULL,
    monthly_contribution DECIMAL(12,2),
    ai_recommendation TEXT,
    status VARCHAR(50) DEFAULT 'in_progress',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- AI LIFE MAP DASHBOARD MODULE
-- ========================================

-- Life Area Metrics
CREATE TABLE life_area_metrics (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    metric_date DATE NOT NULL,
    area VARCHAR(100) NOT NULL,
    score INTEGER NOT NULL,
    trend VARCHAR(20),
    ai_insights TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_life_metrics_user ON life_area_metrics(user_id);
CREATE INDEX idx_life_metrics_date ON life_area_metrics(metric_date);

-- AI Weekly Summaries
CREATE TABLE ai_weekly_summaries (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    week_start DATE NOT NULL,
    week_end DATE NOT NULL,
    finance_summary TEXT,
    health_summary TEXT,
    productivity_summary TEXT,
    social_summary TEXT,
    overall_insights TEXT,
    improvement_suggestions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_weekly_summaries_user ON ai_weekly_summaries(user_id);

-- Life Balance Tracking
CREATE TABLE life_balance_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    log_date DATE NOT NULL,
    work_hours DECIMAL(4,2),
    health_hours DECIMAL(4,2),
    social_hours DECIMAL(4,2),
    learning_hours DECIMAL(4,2),
    personal_hours DECIMAL(4,2),
    balance_score INTEGER,
    ai_feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- PRIVACY & BACKUP CENTER ENHANCEMENTS
-- ========================================

-- Encrypted Cloud Backups
CREATE TABLE cloud_backups (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    backup_name VARCHAR(250) NOT NULL,
    backup_size BIGINT,
    encryption_key_hash VARCHAR(255),
    cloud_provider VARCHAR(100),
    backup_url TEXT,
    modules_included TEXT,
    backup_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_date TIMESTAMP,
    status VARCHAR(50) DEFAULT 'active'
);

CREATE INDEX idx_cloud_backups_user ON cloud_backups(user_id);

-- Data Export Logs
CREATE TABLE data_export_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    export_type VARCHAR(100) NOT NULL,
    modules_exported TEXT,
    file_format VARCHAR(50),
    file_size BIGINT,
    export_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- LIFEHUB AI ADVISOR 2.0
-- ========================================

-- AI Chat Contexts (Enhanced)
CREATE TABLE ai_chat_contexts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    context_type VARCHAR(100) NOT NULL,
    context_data JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- AI Daily Briefings (Enhanced)
CREATE TABLE ai_daily_briefings_v2 (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    briefing_date DATE NOT NULL,
    finance_insights TEXT,
    health_insights TEXT,
    productivity_insights TEXT,
    social_insights TEXT,
    action_items TEXT,
    priority_tasks TEXT,
    weather_mood_correlation TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_daily_briefings_v2_user ON ai_daily_briefings_v2(user_id);
CREATE INDEX idx_daily_briefings_v2_date ON ai_daily_briefings_v2(briefing_date);

-- AI Module Connections
CREATE TABLE ai_module_connections (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    module_from VARCHAR(100) NOT NULL,
    module_to VARCHAR(100) NOT NULL,
    connection_type VARCHAR(100),
    insight TEXT,
    strength_score INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Grant permissions
GRANT ALL ON ALL TABLES IN SCHEMA public TO CURRENT_USER;
GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO CURRENT_USER;
