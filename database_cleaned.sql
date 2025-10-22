--
-- PostgreSQL database dump
--

-- Dumped from database version 16.9 (165f042)
-- Dumped by pg_dump version 17.5

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: public; Type: SCHEMA; Schema: -; Owner: neondb_owner
--

-- *not* creating schema, since initdb creates it


ALTER SCHEMA public OWNER TO neondb_owner;

--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: neondb_owner
--

COMMENT ON SCHEMA public IS '';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: accounts; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.accounts (
    id integer NOT NULL,
    user_id integer NOT NULL,
    account_name character varying(150) NOT NULL,
    account_type character varying(50) NOT NULL,
    balance numeric(12,2) DEFAULT 0.00,
    currency character varying(10) DEFAULT 'USD'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT accounts_account_type_check CHECK (((account_type)::text = ANY ((ARRAY['checking'::character varying, 'savings'::character varying, 'credit_card'::character varying, 'investment'::character varying, 'cash'::character varying, 'other'::character varying])::text[])))
);


ALTER TABLE public.accounts OWNER TO neondb_owner;

--
-- Name: accounts_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.accounts_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.accounts_id_seq OWNER TO neondb_owner;

--
-- Name: accounts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.accounts_id_seq OWNED BY public.accounts.id;


--
-- Name: activity_logs; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.activity_logs (
    id integer NOT NULL,
    user_id integer,
    action character varying(100) NOT NULL,
    entity_type character varying(100),
    entity_id integer,
    description text,
    ip_address character varying(50),
    user_agent text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.activity_logs OWNER TO neondb_owner;

--
-- Name: activity_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.activity_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.activity_logs_id_seq OWNER TO neondb_owner;

--
-- Name: activity_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.activity_logs_id_seq OWNED BY public.activity_logs.id;


--
-- Name: ai_briefings; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.ai_briefings (
    id integer NOT NULL,
    user_id integer NOT NULL,
    briefing_date date NOT NULL,
    briefing_type character varying(50),
    summary text NOT NULL,
    insights text,
    recommendations text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.ai_briefings OWNER TO neondb_owner;

--
-- Name: ai_briefings_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.ai_briefings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ai_briefings_id_seq OWNER TO neondb_owner;

--
-- Name: ai_briefings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.ai_briefings_id_seq OWNED BY public.ai_briefings.id;


--
-- Name: ai_chat_contexts; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.ai_chat_contexts (
    id integer NOT NULL,
    user_id integer NOT NULL,
    context_type character varying(100) NOT NULL,
    context_data jsonb,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.ai_chat_contexts OWNER TO neondb_owner;

--
-- Name: ai_chat_contexts_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.ai_chat_contexts_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ai_chat_contexts_id_seq OWNER TO neondb_owner;

--
-- Name: ai_chat_contexts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.ai_chat_contexts_id_seq OWNED BY public.ai_chat_contexts.id;


--
-- Name: ai_conversations; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.ai_conversations (
    id integer NOT NULL,
    user_id integer NOT NULL,
    conversation_title character varying(200),
    started_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    ended_at timestamp without time zone,
    message_count integer DEFAULT 0
);


ALTER TABLE public.ai_conversations OWNER TO neondb_owner;

--
-- Name: ai_conversations_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.ai_conversations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ai_conversations_id_seq OWNER TO neondb_owner;

--
-- Name: ai_conversations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.ai_conversations_id_seq OWNED BY public.ai_conversations.id;


--
-- Name: ai_daily_briefings_v2; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.ai_daily_briefings_v2 (
    id integer NOT NULL,
    user_id integer NOT NULL,
    briefing_date date NOT NULL,
    finance_insights text,
    health_insights text,
    productivity_insights text,
    social_insights text,
    action_items text,
    priority_tasks text,
    weather_mood_correlation text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.ai_daily_briefings_v2 OWNER TO neondb_owner;

--
-- Name: ai_daily_briefings_v2_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.ai_daily_briefings_v2_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ai_daily_briefings_v2_id_seq OWNER TO neondb_owner;

--
-- Name: ai_daily_briefings_v2_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.ai_daily_briefings_v2_id_seq OWNED BY public.ai_daily_briefings_v2.id;


--
-- Name: ai_messages; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.ai_messages (
    id integer NOT NULL,
    conversation_id integer NOT NULL,
    user_id integer NOT NULL,
    role character varying(20) NOT NULL,
    message_content text NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT ai_messages_role_check CHECK (((role)::text = ANY ((ARRAY['user'::character varying, 'assistant'::character varying, 'system'::character varying])::text[])))
);


ALTER TABLE public.ai_messages OWNER TO neondb_owner;

--
-- Name: ai_messages_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.ai_messages_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ai_messages_id_seq OWNER TO neondb_owner;

--
-- Name: ai_messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.ai_messages_id_seq OWNED BY public.ai_messages.id;


--
-- Name: ai_module_connections; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.ai_module_connections (
    id integer NOT NULL,
    user_id integer NOT NULL,
    module_from character varying(100) NOT NULL,
    module_to character varying(100) NOT NULL,
    connection_type character varying(100),
    insight text,
    strength_score integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.ai_module_connections OWNER TO neondb_owner;

--
-- Name: ai_module_connections_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.ai_module_connections_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ai_module_connections_id_seq OWNER TO neondb_owner;

--
-- Name: ai_module_connections_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.ai_module_connections_id_seq OWNED BY public.ai_module_connections.id;


--
-- Name: ai_reports; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.ai_reports (
    id integer NOT NULL,
    user_id integer NOT NULL,
    report_type character varying(50) NOT NULL,
    report_date date NOT NULL,
    report_content text NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.ai_reports OWNER TO neondb_owner;

--
-- Name: ai_reports_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.ai_reports_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ai_reports_id_seq OWNER TO neondb_owner;

--
-- Name: ai_reports_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.ai_reports_id_seq OWNED BY public.ai_reports.id;


--
-- Name: ai_weekly_summaries; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.ai_weekly_summaries (
    id integer NOT NULL,
    user_id integer NOT NULL,
    week_start date NOT NULL,
    week_end date NOT NULL,
    finance_summary text,
    health_summary text,
    productivity_summary text,
    social_summary text,
    overall_insights text,
    improvement_suggestions text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.ai_weekly_summaries OWNER TO neondb_owner;

--
-- Name: ai_weekly_summaries_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.ai_weekly_summaries_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ai_weekly_summaries_id_seq OWNER TO neondb_owner;

--
-- Name: ai_weekly_summaries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.ai_weekly_summaries_id_seq OWNED BY public.ai_weekly_summaries.id;


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
-- Name: bill_payments; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.bill_payments (
    id integer NOT NULL,
    bill_id integer NOT NULL,
    user_id integer NOT NULL,
    payment_amount numeric(10,2) NOT NULL,
    payment_date date NOT NULL,
    payment_method character varying(100),
    confirmation_number character varying(200),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.bill_payments OWNER TO neondb_owner;

--
-- Name: bill_payments_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.bill_payments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bill_payments_id_seq OWNER TO neondb_owner;

--
-- Name: bill_payments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.bill_payments_id_seq OWNED BY public.bill_payments.id;


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
-- Name: books; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.books (
    id integer NOT NULL,
    user_id integer NOT NULL,
    book_title character varying(200) NOT NULL,
    author character varying(150),
    isbn character varying(20),
    pages integer,
    current_page integer DEFAULT 0,
    status character varying(50) DEFAULT 'to_read'::character varying,
    started_date date,
    finished_date date,
    rating integer,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT books_rating_check CHECK (((rating >= 1) AND (rating <= 5)))
);


ALTER TABLE public.books OWNER TO neondb_owner;

--
-- Name: books_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.books_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.books_id_seq OWNER TO neondb_owner;

--
-- Name: books_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.books_id_seq OWNED BY public.books.id;


--
-- Name: breathing_exercises; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.breathing_exercises (
    id integer NOT NULL,
    user_id integer NOT NULL,
    exercise_date timestamp without time zone NOT NULL,
    exercise_type character varying(100),
    duration_minutes integer,
    breaths_count integer,
    feeling_before integer,
    feeling_after integer,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT breathing_exercises_feeling_after_check CHECK (((feeling_after >= 1) AND (feeling_after <= 10))),
    CONSTRAINT breathing_exercises_feeling_before_check CHECK (((feeling_before >= 1) AND (feeling_before <= 10)))
);


ALTER TABLE public.breathing_exercises OWNER TO neondb_owner;

--
-- Name: breathing_exercises_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.breathing_exercises_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.breathing_exercises_id_seq OWNER TO neondb_owner;

--
-- Name: breathing_exercises_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.breathing_exercises_id_seq OWNED BY public.breathing_exercises.id;


--
-- Name: budget_envelopes; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.budget_envelopes (
    id integer NOT NULL,
    user_id integer NOT NULL,
    name character varying(150) NOT NULL,
    category character varying(100),
    monthly_allocation numeric(10,2) DEFAULT 0 NOT NULL,
    current_balance numeric(10,2) DEFAULT 0 NOT NULL,
    color character varying(20),
    icon character varying(50),
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.budget_envelopes OWNER TO neondb_owner;

--
-- Name: budget_envelopes_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.budget_envelopes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.budget_envelopes_id_seq OWNER TO neondb_owner;

--
-- Name: budget_envelopes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.budget_envelopes_id_seq OWNED BY public.budget_envelopes.id;


--
-- Name: budget_transactions; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.budget_transactions (
    id integer NOT NULL,
    envelope_id integer NOT NULL,
    user_id integer NOT NULL,
    amount numeric(10,2) NOT NULL,
    transaction_type character varying(20) NOT NULL,
    description text,
    transaction_date date NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.budget_transactions OWNER TO neondb_owner;

--
-- Name: budget_transactions_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.budget_transactions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.budget_transactions_id_seq OWNER TO neondb_owner;

--
-- Name: budget_transactions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.budget_transactions_id_seq OWNED BY public.budget_transactions.id;


--
-- Name: budgets; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.budgets (
    id integer NOT NULL,
    user_id integer NOT NULL,
    budget_name character varying(150) NOT NULL,
    month integer NOT NULL,
    year integer NOT NULL,
    total_budget numeric(12,2) DEFAULT 0.00,
    category character varying(100),
    category_limit numeric(12,2),
    monthly_limit numeric(12,2),
    spent_amount numeric(12,2) DEFAULT 0.00,
    notes text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT budgets_month_check CHECK (((month >= 1) AND (month <= 12)))
);


ALTER TABLE public.budgets OWNER TO neondb_owner;

--
-- Name: budgets_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.budgets_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.budgets_id_seq OWNER TO neondb_owner;

--
-- Name: budgets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.budgets_id_seq OWNED BY public.budgets.id;


--
-- Name: calendar_connections; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.calendar_connections (
    id integer NOT NULL,
    user_id integer NOT NULL,
    provider character varying(50) NOT NULL,
    access_token text,
    refresh_token text,
    calendar_id character varying(255),
    calendar_name character varying(150),
    is_active boolean DEFAULT true,
    last_synced timestamp without time zone,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.calendar_connections OWNER TO neondb_owner;

--
-- Name: calendar_connections_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.calendar_connections_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.calendar_connections_id_seq OWNER TO neondb_owner;

--
-- Name: calendar_connections_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.calendar_connections_id_seq OWNED BY public.calendar_connections.id;


--
-- Name: calendar_events; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.calendar_events (
    id integer NOT NULL,
    user_id integer NOT NULL,
    title character varying(200) NOT NULL,
    description text,
    start_time timestamp without time zone NOT NULL,
    end_time timestamp without time zone,
    location character varying(255),
    event_type character varying(50),
    all_day boolean DEFAULT false,
    reminder_minutes integer,
    color character varying(20),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.calendar_events OWNER TO neondb_owner;

--
-- Name: calendar_events_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.calendar_events_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.calendar_events_id_seq OWNER TO neondb_owner;

--
-- Name: calendar_events_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.calendar_events_id_seq OWNED BY public.calendar_events.id;


--
-- Name: calendar_sync_logs; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.calendar_sync_logs (
    id integer NOT NULL,
    sync_setting_id integer NOT NULL,
    user_id integer NOT NULL,
    sync_direction character varying(50),
    events_synced integer DEFAULT 0,
    status character varying(50),
    error_message text,
    sync_started_at timestamp without time zone,
    sync_completed_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.calendar_sync_logs OWNER TO neondb_owner;

--
-- Name: calendar_sync_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.calendar_sync_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.calendar_sync_logs_id_seq OWNER TO neondb_owner;

--
-- Name: calendar_sync_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.calendar_sync_logs_id_seq OWNED BY public.calendar_sync_logs.id;


--
-- Name: calendar_sync_settings; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.calendar_sync_settings (
    id integer NOT NULL,
    user_id integer NOT NULL,
    provider character varying(50) NOT NULL,
    calendar_name character varying(200),
    sync_enabled boolean DEFAULT true,
    sync_direction character varying(50) DEFAULT 'bidirectional'::character varying,
    last_sync_at timestamp without time zone,
    access_token text,
    refresh_token text,
    token_expires_at timestamp without time zone,
    calendar_id character varying(200),
    settings text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.calendar_sync_settings OWNER TO neondb_owner;

--
-- Name: calendar_sync_settings_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.calendar_sync_settings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.calendar_sync_settings_id_seq OWNER TO neondb_owner;

--
-- Name: calendar_sync_settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.calendar_sync_settings_id_seq OWNED BY public.calendar_sync_settings.id;


--
-- Name: career_certifications; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.career_certifications (
    id integer NOT NULL,
    user_id integer NOT NULL,
    certification_name character varying(200) NOT NULL,
    issuing_organization character varying(200),
    issue_date date,
    expiry_date date,
    credential_id character varying(150),
    credential_url text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.career_certifications OWNER TO neondb_owner;

--
-- Name: career_certifications_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.career_certifications_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.career_certifications_id_seq OWNER TO neondb_owner;

--
-- Name: career_certifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.career_certifications_id_seq OWNED BY public.career_certifications.id;


--
-- Name: career_projects; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.career_projects (
    id integer NOT NULL,
    user_id integer NOT NULL,
    project_name character varying(200) NOT NULL,
    description text,
    start_date date,
    end_date date,
    status character varying(50) DEFAULT 'in_progress'::character varying,
    skills_used text,
    project_url text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.career_projects OWNER TO neondb_owner;

--
-- Name: career_projects_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.career_projects_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.career_projects_id_seq OWNER TO neondb_owner;

--
-- Name: career_projects_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.career_projects_id_seq OWNED BY public.career_projects.id;


--
-- Name: career_tasks; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.career_tasks (
    id integer NOT NULL,
    project_id integer,
    user_id integer NOT NULL,
    task_name character varying(200) NOT NULL,
    description text,
    status character varying(50) DEFAULT 'pending'::character varying,
    priority character varying(20),
    due_date date,
    completed_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.career_tasks OWNER TO neondb_owner;

--
-- Name: career_tasks_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.career_tasks_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.career_tasks_id_seq OWNER TO neondb_owner;

--
-- Name: career_tasks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.career_tasks_id_seq OWNED BY public.career_tasks.id;


--
-- Name: chat_messages; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.chat_messages (
    id integer NOT NULL,
    session_id integer NOT NULL,
    user_id integer NOT NULL,
    role character varying(20) NOT NULL,
    message_text text NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.chat_messages OWNER TO neondb_owner;

--
-- Name: chat_messages_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.chat_messages_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.chat_messages_id_seq OWNER TO neondb_owner;

--
-- Name: chat_messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.chat_messages_id_seq OWNED BY public.chat_messages.id;


--
-- Name: chat_sessions; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.chat_sessions (
    id integer NOT NULL,
    user_id integer NOT NULL,
    session_name character varying(200),
    started_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    ended_at timestamp without time zone
);


ALTER TABLE public.chat_sessions OWNER TO neondb_owner;

--
-- Name: chat_sessions_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.chat_sessions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.chat_sessions_id_seq OWNER TO neondb_owner;

--
-- Name: chat_sessions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.chat_sessions_id_seq OWNED BY public.chat_sessions.id;


--
-- Name: cloud_backups; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.cloud_backups (
    id integer NOT NULL,
    user_id integer NOT NULL,
    backup_name character varying(250) NOT NULL,
    backup_size bigint,
    encryption_key_hash character varying(255),
    cloud_provider character varying(100),
    backup_url text,
    modules_included text,
    backup_date timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    expiry_date timestamp without time zone,
    status character varying(50) DEFAULT 'active'::character varying
);


ALTER TABLE public.cloud_backups OWNER TO neondb_owner;

--
-- Name: cloud_backups_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.cloud_backups_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cloud_backups_id_seq OWNER TO neondb_owner;

--
-- Name: cloud_backups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.cloud_backups_id_seq OWNED BY public.cloud_backups.id;


--
-- Name: contact_interactions; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.contact_interactions (
    id integer NOT NULL,
    contact_id integer NOT NULL,
    user_id integer NOT NULL,
    interaction_type character varying(50),
    interaction_date date NOT NULL,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.contact_interactions OWNER TO neondb_owner;

--
-- Name: contact_interactions_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.contact_interactions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.contact_interactions_id_seq OWNER TO neondb_owner;

--
-- Name: contact_interactions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.contact_interactions_id_seq OWNED BY public.contact_interactions.id;


--
-- Name: contact_reminders; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.contact_reminders (
    id integer NOT NULL,
    contact_id integer NOT NULL,
    user_id integer NOT NULL,
    reminder_type character varying(50),
    frequency_days integer,
    last_contact_date date,
    next_reminder_date date,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.contact_reminders OWNER TO neondb_owner;

--
-- Name: contact_reminders_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.contact_reminders_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.contact_reminders_id_seq OWNER TO neondb_owner;

--
-- Name: contact_reminders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.contact_reminders_id_seq OWNED BY public.contact_reminders.id;


--
-- Name: contacts; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.contacts (
    id integer NOT NULL,
    user_id integer NOT NULL,
    first_name character varying(100) NOT NULL,
    last_name character varying(100),
    email character varying(150),
    phone character varying(20),
    company character varying(150),
    job_title character varying(150),
    address text,
    notes text,
    category character varying(100),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.contacts OWNER TO neondb_owner;

--
-- Name: contacts_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.contacts_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.contacts_id_seq OWNER TO neondb_owner;

--
-- Name: contacts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.contacts_id_seq OWNED BY public.contacts.id;


--
-- Name: courses; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.courses (
    id integer NOT NULL,
    user_id integer NOT NULL,
    title character varying(250) NOT NULL,
    platform character varying(100),
    instructor character varying(200),
    course_url text,
    status character varying(50) DEFAULT 'not_started'::character varying,
    progress integer DEFAULT 0,
    start_date date,
    completion_date date,
    certificate_url text,
    rating integer,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.courses OWNER TO neondb_owner;

--
-- Name: courses_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.courses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.courses_id_seq OWNER TO neondb_owner;

--
-- Name: courses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.courses_id_seq OWNED BY public.courses.id;


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
-- Name: data_export_logs; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.data_export_logs (
    id integer NOT NULL,
    user_id integer NOT NULL,
    export_type character varying(100) NOT NULL,
    modules_exported text,
    file_format character varying(50),
    file_size bigint,
    export_date timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.data_export_logs OWNER TO neondb_owner;

--
-- Name: data_export_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.data_export_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.data_export_logs_id_seq OWNER TO neondb_owner;

--
-- Name: data_export_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.data_export_logs_id_seq OWNED BY public.data_export_logs.id;


--
-- Name: debt_payments; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.debt_payments (
    id integer NOT NULL,
    debt_id integer NOT NULL,
    user_id integer NOT NULL,
    payment_amount numeric(10,2) NOT NULL,
    payment_date date NOT NULL,
    principal_paid numeric(10,2),
    interest_paid numeric(10,2),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.debt_payments OWNER TO neondb_owner;

--
-- Name: debt_payments_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.debt_payments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.debt_payments_id_seq OWNER TO neondb_owner;

--
-- Name: debt_payments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.debt_payments_id_seq OWNED BY public.debt_payments.id;


--
-- Name: debts; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.debts (
    id integer NOT NULL,
    user_id integer NOT NULL,
    debt_name character varying(150) NOT NULL,
    creditor character varying(150),
    principal_amount numeric(12,2) NOT NULL,
    current_balance numeric(12,2) NOT NULL,
    interest_rate numeric(5,2),
    minimum_payment numeric(10,2),
    due_date date,
    status character varying(50) DEFAULT 'active'::character varying,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.debts OWNER TO neondb_owner;

--
-- Name: debts_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.debts_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.debts_id_seq OWNER TO neondb_owner;

--
-- Name: debts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.debts_id_seq OWNED BY public.debts.id;


--
-- Name: diet_meals; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.diet_meals (
    id integer NOT NULL,
    plan_id integer,
    user_id integer NOT NULL,
    meal_date date NOT NULL,
    meal_type character varying(50) NOT NULL,
    meal_name character varying(150),
    calories integer,
    protein_g numeric(6,2),
    carbs_g numeric(6,2),
    fat_g numeric(6,2),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.diet_meals OWNER TO neondb_owner;

--
-- Name: diet_meals_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.diet_meals_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.diet_meals_id_seq OWNER TO neondb_owner;

--
-- Name: diet_meals_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.diet_meals_id_seq OWNED BY public.diet_meals.id;


--
-- Name: diet_plans; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.diet_plans (
    id integer NOT NULL,
    user_id integer,
    date date NOT NULL,
    meal_type character varying(50),
    food_item character varying(255),
    calories integer,
    protein numeric(10,2),
    carbs numeric(10,2),
    fats numeric(10,2),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.diet_plans OWNER TO neondb_owner;

--
-- Name: diet_plans_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.diet_plans_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.diet_plans_id_seq OWNER TO neondb_owner;

--
-- Name: diet_plans_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.diet_plans_id_seq OWNED BY public.diet_plans.id;


--
-- Name: document_summaries; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.document_summaries (
    id integer NOT NULL,
    user_id integer NOT NULL,
    title character varying(250) NOT NULL,
    original_content text,
    ai_summary text,
    key_points text,
    document_type character varying(50),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.document_summaries OWNER TO neondb_owner;

--
-- Name: document_summaries_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.document_summaries_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.document_summaries_id_seq OWNER TO neondb_owner;

--
-- Name: document_summaries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.document_summaries_id_seq OWNED BY public.document_summaries.id;


--
-- Name: document_versions; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.document_versions (
    id integer NOT NULL,
    document_id integer NOT NULL,
    user_id integer NOT NULL,
    version_number integer NOT NULL,
    file_path text NOT NULL,
    change_notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.document_versions OWNER TO neondb_owner;

--
-- Name: document_versions_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.document_versions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.document_versions_id_seq OWNER TO neondb_owner;

--
-- Name: document_versions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.document_versions_id_seq OWNED BY public.document_versions.id;


--
-- Name: documents; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.documents (
    id integer NOT NULL,
    user_id integer NOT NULL,
    document_name character varying(200) NOT NULL,
    document_type character varying(100),
    file_path text,
    file_size integer,
    category character varying(100),
    tags text,
    uploaded_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.documents OWNER TO neondb_owner;

--
-- Name: documents_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.documents_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.documents_id_seq OWNER TO neondb_owner;

--
-- Name: documents_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.documents_id_seq OWNED BY public.documents.id;


--
-- Name: emergency_contacts; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.emergency_contacts (
    id integer NOT NULL,
    user_id integer NOT NULL,
    contact_name character varying(150) NOT NULL,
    relationship character varying(100),
    phone_primary character varying(20) NOT NULL,
    phone_secondary character varying(20),
    email character varying(150),
    address text,
    priority_order integer DEFAULT 1,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.emergency_contacts OWNER TO neondb_owner;

--
-- Name: emergency_contacts_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.emergency_contacts_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.emergency_contacts_id_seq OWNER TO neondb_owner;

--
-- Name: emergency_contacts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.emergency_contacts_id_seq OWNED BY public.emergency_contacts.id;


--
-- Name: emergency_log; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.emergency_log (
    id integer NOT NULL,
    user_id integer NOT NULL,
    activated_at timestamp without time zone NOT NULL,
    deactivated_at timestamp without time zone,
    reason text,
    contacts_notified text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.emergency_log OWNER TO neondb_owner;

--
-- Name: emergency_log_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.emergency_log_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.emergency_log_id_seq OWNER TO neondb_owner;

--
-- Name: emergency_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.emergency_log_id_seq OWNED BY public.emergency_log.id;


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
-- Name: event_budget_items; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.event_budget_items (
    id integer NOT NULL,
    event_id integer NOT NULL,
    category character varying(100) NOT NULL,
    description character varying(300),
    estimated_cost numeric(10,2),
    actual_cost numeric(10,2),
    is_paid boolean DEFAULT false,
    payment_date date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.event_budget_items OWNER TO neondb_owner;

--
-- Name: event_budget_items_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.event_budget_items_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.event_budget_items_id_seq OWNER TO neondb_owner;

--
-- Name: event_budget_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.event_budget_items_id_seq OWNED BY public.event_budget_items.id;


--
-- Name: event_checklists; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.event_checklists (
    id integer NOT NULL,
    event_id integer NOT NULL,
    task_name character varying(200) NOT NULL,
    completed boolean DEFAULT false,
    due_date date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.event_checklists OWNER TO neondb_owner;

--
-- Name: event_checklists_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.event_checklists_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.event_checklists_id_seq OWNER TO neondb_owner;

--
-- Name: event_checklists_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.event_checklists_id_seq OWNED BY public.event_checklists.id;


--
-- Name: event_guests; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.event_guests (
    id integer NOT NULL,
    event_id integer NOT NULL,
    guest_name character varying(150) NOT NULL,
    email character varying(150),
    phone character varying(20),
    rsvp_status character varying(50) DEFAULT 'pending'::character varying,
    plus_one boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.event_guests OWNER TO neondb_owner;

--
-- Name: event_guests_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.event_guests_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.event_guests_id_seq OWNER TO neondb_owner;

--
-- Name: event_guests_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.event_guests_id_seq OWNED BY public.event_guests.id;


--
-- Name: events; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.events (
    id integer NOT NULL,
    user_id integer NOT NULL,
    event_name character varying(200) NOT NULL,
    event_type character varying(100),
    event_date date NOT NULL,
    location character varying(255),
    description text,
    budget numeric(10,2),
    status character varying(50) DEFAULT 'planning'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.events OWNER TO neondb_owner;

--
-- Name: events_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.events_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.events_id_seq OWNER TO neondb_owner;

--
-- Name: events_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.events_id_seq OWNED BY public.events.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.failed_jobs (
    id integer NOT NULL,
    queue character varying(100) NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.failed_jobs OWNER TO neondb_owner;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.failed_jobs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO neondb_owner;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: family_members; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.family_members (
    id integer NOT NULL,
    user_id integer NOT NULL,
    member_name character varying(150) NOT NULL,
    relationship character varying(100),
    birth_date date,
    email character varying(150),
    phone character varying(20),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.family_members OWNER TO neondb_owner;

--
-- Name: family_members_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.family_members_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.family_members_id_seq OWNER TO neondb_owner;

--
-- Name: family_members_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.family_members_id_seq OWNED BY public.family_members.id;


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
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    transaction_date date DEFAULT CURRENT_DATE
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
-- Name: financial_accounts; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.financial_accounts (
    id integer NOT NULL,
    user_id integer,
    account_name character varying(255) NOT NULL,
    account_type character varying(100),
    current_balance numeric(12,2) DEFAULT 0,
    currency character varying(10) DEFAULT 'USD'::character varying,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.financial_accounts OWNER TO neondb_owner;

--
-- Name: financial_accounts_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.financial_accounts_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.financial_accounts_id_seq OWNER TO neondb_owner;

--
-- Name: financial_accounts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.financial_accounts_id_seq OWNED BY public.financial_accounts.id;


--
-- Name: financial_forecasts; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.financial_forecasts (
    id integer NOT NULL,
    user_id integer NOT NULL,
    forecast_date date NOT NULL,
    predicted_balance numeric(12,2),
    predicted_income numeric(12,2),
    predicted_expenses numeric(12,2),
    confidence_level character varying(20),
    scenario_type character varying(50),
    risks text,
    recommendations text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.financial_forecasts OWNER TO neondb_owner;

--
-- Name: financial_forecasts_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.financial_forecasts_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.financial_forecasts_id_seq OWNER TO neondb_owner;

--
-- Name: financial_forecasts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.financial_forecasts_id_seq OWNED BY public.financial_forecasts.id;


--
-- Name: financial_projections; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.financial_projections (
    id integer NOT NULL,
    user_id integer NOT NULL,
    projection_type character varying(100) NOT NULL,
    target_amount numeric(12,2) NOT NULL,
    current_amount numeric(12,2),
    target_date date NOT NULL,
    monthly_contribution numeric(12,2),
    ai_recommendation text,
    status character varying(50) DEFAULT 'in_progress'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.financial_projections OWNER TO neondb_owner;

--
-- Name: financial_projections_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.financial_projections_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.financial_projections_id_seq OWNER TO neondb_owner;

--
-- Name: financial_projections_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.financial_projections_id_seq OWNED BY public.financial_projections.id;


--
-- Name: flashcards; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.flashcards (
    id integer NOT NULL,
    user_id integer NOT NULL,
    course_id integer,
    front_text text NOT NULL,
    back_text text NOT NULL,
    category character varying(100),
    difficulty_level character varying(20),
    times_reviewed integer DEFAULT 0,
    times_correct integer DEFAULT 0,
    last_reviewed timestamp without time zone,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.flashcards OWNER TO neondb_owner;

--
-- Name: flashcards_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.flashcards_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.flashcards_id_seq OWNER TO neondb_owner;

--
-- Name: flashcards_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.flashcards_id_seq OWNED BY public.flashcards.id;


--
-- Name: freelance_clients; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.freelance_clients (
    id integer NOT NULL,
    user_id integer,
    name character varying(255) NOT NULL,
    email character varying(255),
    phone character varying(50),
    company character varying(255),
    address text,
    notes text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.freelance_clients OWNER TO neondb_owner;

--
-- Name: freelance_clients_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.freelance_clients_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.freelance_clients_id_seq OWNER TO neondb_owner;

--
-- Name: freelance_clients_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.freelance_clients_id_seq OWNED BY public.freelance_clients.id;


--
-- Name: freelance_invoices; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.freelance_invoices (
    id integer NOT NULL,
    user_id integer,
    client_id integer,
    project_id integer,
    invoice_number character varying(100),
    amount numeric(12,2),
    status character varying(50) DEFAULT 'draft'::character varying,
    issue_date date,
    due_date date,
    paid_date date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.freelance_invoices OWNER TO neondb_owner;

--
-- Name: freelance_invoices_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.freelance_invoices_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.freelance_invoices_id_seq OWNER TO neondb_owner;

--
-- Name: freelance_invoices_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.freelance_invoices_id_seq OWNED BY public.freelance_invoices.id;


--
-- Name: freelance_projects; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.freelance_projects (
    id integer NOT NULL,
    user_id integer,
    client_id integer,
    name character varying(255) NOT NULL,
    description text,
    status character varying(50) DEFAULT 'active'::character varying,
    budget numeric(12,2),
    hourly_rate numeric(10,2),
    start_date date,
    end_date date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.freelance_projects OWNER TO neondb_owner;

--
-- Name: freelance_projects_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.freelance_projects_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.freelance_projects_id_seq OWNER TO neondb_owner;

--
-- Name: freelance_projects_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.freelance_projects_id_seq OWNED BY public.freelance_projects.id;


--
-- Name: gifts; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.gifts (
    id integer NOT NULL,
    user_id integer NOT NULL,
    gift_name character varying(200) NOT NULL,
    recipient_name character varying(150) NOT NULL,
    occasion character varying(100),
    provider_link text,
    price numeric(10,2),
    notes text,
    event_id integer,
    event_type character varying(50),
    purchased boolean DEFAULT false,
    purchase_date date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.gifts OWNER TO neondb_owner;

--
-- Name: gifts_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.gifts_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.gifts_id_seq OWNER TO neondb_owner;

--
-- Name: gifts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.gifts_id_seq OWNED BY public.gifts.id;


--
-- Name: goal_activities; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.goal_activities (
    id integer NOT NULL,
    goal_id integer,
    user_id integer NOT NULL,
    activity_name character varying(200) NOT NULL,
    activity_date date NOT NULL,
    impact_value numeric(5,2),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.goal_activities OWNER TO neondb_owner;

--
-- Name: goal_activities_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.goal_activities_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.goal_activities_id_seq OWNER TO neondb_owner;

--
-- Name: goal_activities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.goal_activities_id_seq OWNED BY public.goal_activities.id;


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
-- Name: grocery_items; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.grocery_items (
    id integer NOT NULL,
    grocery_list_id integer,
    item_name character varying(200) NOT NULL,
    quantity character varying(50),
    category character varying(100),
    purchased boolean DEFAULT false,
    price numeric(10,2),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.grocery_items OWNER TO neondb_owner;

--
-- Name: grocery_items_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.grocery_items_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.grocery_items_id_seq OWNER TO neondb_owner;

--
-- Name: grocery_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.grocery_items_id_seq OWNED BY public.grocery_items.id;


--
-- Name: grocery_lists; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.grocery_lists (
    id integer NOT NULL,
    user_id integer NOT NULL,
    list_name character varying(150) NOT NULL,
    item_name character varying(200) NOT NULL,
    quantity character varying(50),
    category character varying(100),
    estimated_cost numeric(8,2),
    purchased boolean DEFAULT false,
    purchased_date date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.grocery_lists OWNER TO neondb_owner;

--
-- Name: grocery_lists_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.grocery_lists_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.grocery_lists_id_seq OWNER TO neondb_owner;

--
-- Name: grocery_lists_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.grocery_lists_id_seq OWNED BY public.grocery_lists.id;


--
-- Name: gym_exercises; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.gym_exercises (
    id integer NOT NULL,
    routine_id integer,
    user_id integer,
    exercise_name character varying(255) NOT NULL,
    sets integer,
    reps integer,
    weight numeric(10,2),
    duration integer,
    rest_time integer,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.gym_exercises OWNER TO neondb_owner;

--
-- Name: gym_exercises_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.gym_exercises_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.gym_exercises_id_seq OWNER TO neondb_owner;

--
-- Name: gym_exercises_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.gym_exercises_id_seq OWNED BY public.gym_exercises.id;


--
-- Name: gym_routines; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.gym_routines (
    id integer NOT NULL,
    user_id integer,
    routine_name character varying(255) NOT NULL,
    description text,
    muscle_group character varying(100),
    difficulty character varying(50),
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.gym_routines OWNER TO neondb_owner;

--
-- Name: gym_routines_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.gym_routines_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.gym_routines_id_seq OWNER TO neondb_owner;

--
-- Name: gym_routines_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.gym_routines_id_seq OWNED BY public.gym_routines.id;


--
-- Name: gym_sessions; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.gym_sessions (
    id integer NOT NULL,
    user_id integer,
    routine_id integer,
    session_date date NOT NULL,
    duration integer,
    calories_burned integer,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.gym_sessions OWNER TO neondb_owner;

--
-- Name: gym_sessions_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.gym_sessions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.gym_sessions_id_seq OWNER TO neondb_owner;

--
-- Name: gym_sessions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.gym_sessions_id_seq OWNED BY public.gym_sessions.id;


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
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    target_days integer DEFAULT 30
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
-- Name: health_profiles; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.health_profiles (
    id integer NOT NULL,
    user_id integer NOT NULL,
    blood_type character varying(10),
    allergies text,
    chronic_conditions text,
    current_medications text,
    emergency_notes text,
    primary_doctor character varying(150),
    doctor_phone character varying(20),
    insurance_provider character varying(150),
    insurance_policy_number character varying(100),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.health_profiles OWNER TO neondb_owner;

--
-- Name: health_profiles_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.health_profiles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.health_profiles_id_seq OWNER TO neondb_owner;

--
-- Name: health_profiles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.health_profiles_id_seq OWNED BY public.health_profiles.id;


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
-- Name: home_assets; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.home_assets (
    id integer NOT NULL,
    user_id integer NOT NULL,
    asset_name character varying(150) NOT NULL,
    category character varying(100),
    purchase_date date,
    purchase_price numeric(12,2),
    current_value numeric(12,2),
    location character varying(200),
    warranty_expiry date,
    serial_number character varying(100),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.home_assets OWNER TO neondb_owner;

--
-- Name: home_assets_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.home_assets_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.home_assets_id_seq OWNER TO neondb_owner;

--
-- Name: home_assets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.home_assets_id_seq OWNED BY public.home_assets.id;


--
-- Name: household_expense_shares; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.household_expense_shares (
    id integer NOT NULL,
    household_expense_id integer,
    family_member_id integer,
    share_amount numeric(10,2) NOT NULL,
    paid boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.household_expense_shares OWNER TO neondb_owner;

--
-- Name: household_expense_shares_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.household_expense_shares_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.household_expense_shares_id_seq OWNER TO neondb_owner;

--
-- Name: household_expense_shares_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.household_expense_shares_id_seq OWNED BY public.household_expense_shares.id;


--
-- Name: household_expenses; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.household_expenses (
    id integer NOT NULL,
    user_id integer NOT NULL,
    expense_name character varying(200) NOT NULL,
    amount numeric(10,2) NOT NULL,
    expense_date date NOT NULL,
    category character varying(100),
    paid_by integer,
    split_with text,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.household_expenses OWNER TO neondb_owner;

--
-- Name: household_expenses_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.household_expenses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.household_expenses_id_seq OWNER TO neondb_owner;

--
-- Name: household_expenses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.household_expenses_id_seq OWNED BY public.household_expenses.id;


--
-- Name: household_tasks; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.household_tasks (
    id integer NOT NULL,
    user_id integer NOT NULL,
    task_name character varying(200) NOT NULL,
    assigned_to integer,
    frequency character varying(50),
    last_completed date,
    next_due_date date,
    status character varying(50) DEFAULT 'pending'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.household_tasks OWNER TO neondb_owner;

--
-- Name: household_tasks_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.household_tasks_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.household_tasks_id_seq OWNER TO neondb_owner;

--
-- Name: household_tasks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.household_tasks_id_seq OWNED BY public.household_tasks.id;


--
-- Name: interviews; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.interviews (
    id integer NOT NULL,
    job_application_id integer,
    user_id integer NOT NULL,
    interview_type character varying(100),
    interview_date timestamp without time zone NOT NULL,
    interviewer_name character varying(150),
    location character varying(200),
    outcome character varying(50),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.interviews OWNER TO neondb_owner;

--
-- Name: interviews_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.interviews_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.interviews_id_seq OWNER TO neondb_owner;

--
-- Name: interviews_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.interviews_id_seq OWNED BY public.interviews.id;


--
-- Name: investment_portfolio; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.investment_portfolio (
    id integer NOT NULL,
    user_id integer NOT NULL,
    asset_name character varying(200) NOT NULL,
    asset_type character varying(50) NOT NULL,
    symbol character varying(20),
    quantity numeric(20,8) NOT NULL,
    purchase_price numeric(20,2) NOT NULL,
    current_price numeric(20,2),
    purchase_date date NOT NULL,
    broker character varying(100),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.investment_portfolio OWNER TO neondb_owner;

--
-- Name: investment_portfolio_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.investment_portfolio_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.investment_portfolio_id_seq OWNER TO neondb_owner;

--
-- Name: investment_portfolio_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.investment_portfolio_id_seq OWNED BY public.investment_portfolio.id;


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
-- Name: job_applications; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.job_applications (
    id integer NOT NULL,
    user_id integer NOT NULL,
    company_name character varying(200) NOT NULL,
    job_title character varying(200) NOT NULL,
    job_url text,
    application_date date,
    status character varying(50) DEFAULT 'applied'::character varying,
    salary_range character varying(100),
    location character varying(200),
    contact_person character varying(150),
    contact_email character varying(150),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.job_applications OWNER TO neondb_owner;

--
-- Name: job_applications_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.job_applications_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.job_applications_id_seq OWNER TO neondb_owner;

--
-- Name: job_applications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.job_applications_id_seq OWNED BY public.job_applications.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.job_batches (
    id integer NOT NULL,
    name character varying(200) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    completed_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.job_batches OWNER TO neondb_owner;

--
-- Name: job_batches_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.job_batches_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.job_batches_id_seq OWNER TO neondb_owner;

--
-- Name: job_batches_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.job_batches_id_seq OWNED BY public.job_batches.id;


--
-- Name: job_logs; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.job_logs (
    id integer NOT NULL,
    job_id integer,
    status character varying(50) NOT NULL,
    output text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.job_logs OWNER TO neondb_owner;

--
-- Name: job_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.job_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.job_logs_id_seq OWNER TO neondb_owner;

--
-- Name: job_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.job_logs_id_seq OWNED BY public.job_logs.id;


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.jobs (
    id integer NOT NULL,
    queue character varying(100) NOT NULL,
    payload text NOT NULL,
    attempts integer DEFAULT 0,
    reserved_at timestamp without time zone,
    available_at timestamp without time zone NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.jobs OWNER TO neondb_owner;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.jobs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO neondb_owner;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


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
-- Name: knowledge_items; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.knowledge_items (
    id integer NOT NULL,
    user_id integer,
    title character varying(255) NOT NULL,
    content text,
    category character varying(100),
    tags text,
    is_favorite boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.knowledge_items OWNER TO neondb_owner;

--
-- Name: knowledge_items_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.knowledge_items_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.knowledge_items_id_seq OWNER TO neondb_owner;

--
-- Name: knowledge_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.knowledge_items_id_seq OWNED BY public.knowledge_items.id;


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
-- Name: learning_courses; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.learning_courses (
    id integer NOT NULL,
    user_id integer NOT NULL,
    course_name character varying(200) NOT NULL,
    platform character varying(100),
    instructor character varying(150),
    course_url text,
    start_date date,
    target_completion_date date,
    completion_date date,
    progress_percentage integer DEFAULT 0,
    certificate_url text,
    rating integer,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT learning_courses_rating_check CHECK (((rating >= 1) AND (rating <= 5)))
);


ALTER TABLE public.learning_courses OWNER TO neondb_owner;

--
-- Name: learning_courses_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.learning_courses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.learning_courses_id_seq OWNER TO neondb_owner;

--
-- Name: learning_courses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.learning_courses_id_seq OWNED BY public.learning_courses.id;


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
-- Name: learning_notes; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.learning_notes (
    id integer NOT NULL,
    course_id integer,
    user_id integer NOT NULL,
    note_title character varying(200),
    note_content text NOT NULL,
    lesson_number integer,
    tags text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.learning_notes OWNER TO neondb_owner;

--
-- Name: learning_notes_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.learning_notes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.learning_notes_id_seq OWNER TO neondb_owner;

--
-- Name: learning_notes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.learning_notes_id_seq OWNED BY public.learning_notes.id;


--
-- Name: life_advisor_actions; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.life_advisor_actions (
    id integer NOT NULL,
    user_id integer NOT NULL,
    action_type character varying(100) NOT NULL,
    action_description text NOT NULL,
    priority character varying(20),
    status character varying(50) DEFAULT 'pending'::character varying,
    due_date date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    completed_at timestamp without time zone
);


ALTER TABLE public.life_advisor_actions OWNER TO neondb_owner;

--
-- Name: life_advisor_actions_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.life_advisor_actions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.life_advisor_actions_id_seq OWNER TO neondb_owner;

--
-- Name: life_advisor_actions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.life_advisor_actions_id_seq OWNED BY public.life_advisor_actions.id;


--
-- Name: life_advisor_briefings; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.life_advisor_briefings (
    id integer NOT NULL,
    user_id integer NOT NULL,
    briefing_date date NOT NULL,
    priority_tasks text,
    health_summary text,
    financial_summary text,
    upcoming_events text,
    recommendations text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.life_advisor_briefings OWNER TO neondb_owner;

--
-- Name: life_advisor_briefings_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.life_advisor_briefings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.life_advisor_briefings_id_seq OWNER TO neondb_owner;

--
-- Name: life_advisor_briefings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.life_advisor_briefings_id_seq OWNED BY public.life_advisor_briefings.id;


--
-- Name: life_area_metrics; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.life_area_metrics (
    id integer NOT NULL,
    user_id integer NOT NULL,
    metric_date date NOT NULL,
    area character varying(100) NOT NULL,
    score integer NOT NULL,
    trend character varying(20),
    ai_insights text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.life_area_metrics OWNER TO neondb_owner;

--
-- Name: life_area_metrics_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.life_area_metrics_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.life_area_metrics_id_seq OWNER TO neondb_owner;

--
-- Name: life_area_metrics_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.life_area_metrics_id_seq OWNED BY public.life_area_metrics.id;


--
-- Name: life_balance_logs; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.life_balance_logs (
    id integer NOT NULL,
    user_id integer NOT NULL,
    log_date date NOT NULL,
    work_hours numeric(4,2),
    health_hours numeric(4,2),
    social_hours numeric(4,2),
    learning_hours numeric(4,2),
    personal_hours numeric(4,2),
    balance_score integer,
    ai_feedback text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.life_balance_logs OWNER TO neondb_owner;

--
-- Name: life_balance_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.life_balance_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.life_balance_logs_id_seq OWNER TO neondb_owner;

--
-- Name: life_balance_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.life_balance_logs_id_seq OWNED BY public.life_balance_logs.id;


--
-- Name: life_event_predictions; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.life_event_predictions (
    id integer NOT NULL,
    user_id integer NOT NULL,
    event_type character varying(100) NOT NULL,
    predicted_date date,
    confidence_level character varying(20),
    description text,
    recommendations text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.life_event_predictions OWNER TO neondb_owner;

--
-- Name: life_event_predictions_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.life_event_predictions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.life_event_predictions_id_seq OWNER TO neondb_owner;

--
-- Name: life_event_predictions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.life_event_predictions_id_seq OWNED BY public.life_event_predictions.id;


--
-- Name: maintenance_logs; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.maintenance_logs (
    id integer NOT NULL,
    asset_id integer NOT NULL,
    user_id integer NOT NULL,
    maintenance_type character varying(100),
    maintenance_date date NOT NULL,
    cost numeric(10,2),
    performed_by character varying(150),
    notes text,
    next_maintenance_date date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.maintenance_logs OWNER TO neondb_owner;

--
-- Name: maintenance_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.maintenance_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.maintenance_logs_id_seq OWNER TO neondb_owner;

--
-- Name: maintenance_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.maintenance_logs_id_seq OWNED BY public.maintenance_logs.id;


--
-- Name: meal_plans; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.meal_plans (
    id integer NOT NULL,
    user_id integer NOT NULL,
    plan_name character varying(150) NOT NULL,
    start_date date NOT NULL,
    end_date date,
    monday_breakfast integer,
    monday_lunch integer,
    monday_dinner integer,
    tuesday_breakfast integer,
    tuesday_lunch integer,
    tuesday_dinner integer,
    wednesday_breakfast integer,
    wednesday_lunch integer,
    wednesday_dinner integer,
    thursday_breakfast integer,
    thursday_lunch integer,
    thursday_dinner integer,
    friday_breakfast integer,
    friday_lunch integer,
    friday_dinner integer,
    saturday_breakfast integer,
    saturday_lunch integer,
    saturday_dinner integer,
    sunday_breakfast integer,
    sunday_lunch integer,
    sunday_dinner integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.meal_plans OWNER TO neondb_owner;

--
-- Name: meal_plans_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.meal_plans_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.meal_plans_id_seq OWNER TO neondb_owner;

--
-- Name: meal_plans_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.meal_plans_id_seq OWNED BY public.meal_plans.id;


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
-- Name: medication_logs; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.medication_logs (
    id integer NOT NULL,
    medication_id integer NOT NULL,
    user_id integer NOT NULL,
    taken_at timestamp without time zone NOT NULL,
    dosage_taken character varying(100),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.medication_logs OWNER TO neondb_owner;

--
-- Name: medication_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.medication_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.medication_logs_id_seq OWNER TO neondb_owner;

--
-- Name: medication_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.medication_logs_id_seq OWNED BY public.medication_logs.id;


--
-- Name: medications; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.medications (
    id integer NOT NULL,
    user_id integer NOT NULL,
    medication_name character varying(200) NOT NULL,
    dosage character varying(100),
    frequency character varying(100),
    prescribing_doctor character varying(150),
    start_date date,
    end_date date,
    purpose text,
    side_effects text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.medications OWNER TO neondb_owner;

--
-- Name: medications_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.medications_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.medications_id_seq OWNER TO neondb_owner;

--
-- Name: medications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.medications_id_seq OWNED BY public.medications.id;


--
-- Name: meditation_sessions; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.meditation_sessions (
    id integer NOT NULL,
    user_id integer NOT NULL,
    session_date date NOT NULL,
    duration_minutes integer NOT NULL,
    meditation_type character varying(100),
    focus_rating integer,
    calmness_rating integer,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT meditation_sessions_calmness_rating_check CHECK (((calmness_rating >= 1) AND (calmness_rating <= 10))),
    CONSTRAINT meditation_sessions_focus_rating_check CHECK (((focus_rating >= 1) AND (focus_rating <= 10)))
);


ALTER TABLE public.meditation_sessions OWNER TO neondb_owner;

--
-- Name: meditation_sessions_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.meditation_sessions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.meditation_sessions_id_seq OWNER TO neondb_owner;

--
-- Name: meditation_sessions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.meditation_sessions_id_seq OWNED BY public.meditation_sessions.id;


--
-- Name: mood_entries; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.mood_entries (
    id integer NOT NULL,
    user_id integer,
    date date NOT NULL,
    mood character varying(50),
    energy_level integer,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.mood_entries OWNER TO neondb_owner;

--
-- Name: mood_entries_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.mood_entries_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.mood_entries_id_seq OWNER TO neondb_owner;

--
-- Name: mood_entries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.mood_entries_id_seq OWNED BY public.mood_entries.id;


--
-- Name: note_categories; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.note_categories (
    id integer NOT NULL,
    user_id integer NOT NULL,
    name character varying(100) NOT NULL,
    color character varying(20),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.note_categories OWNER TO neondb_owner;

--
-- Name: note_categories_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.note_categories_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.note_categories_id_seq OWNER TO neondb_owner;

--
-- Name: note_categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.note_categories_id_seq OWNED BY public.note_categories.id;


--
-- Name: notes; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.notes (
    id integer NOT NULL,
    user_id integer NOT NULL,
    title character varying(200) NOT NULL,
    content text,
    category character varying(100),
    tags character varying(500),
    is_favorite boolean DEFAULT false,
    is_archived boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.notes OWNER TO neondb_owner;

--
-- Name: notes_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.notes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.notes_id_seq OWNER TO neondb_owner;

--
-- Name: notes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.notes_id_seq OWNED BY public.notes.id;


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
-- Name: oauth_sessions; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.oauth_sessions (
    id integer NOT NULL,
    user_id integer NOT NULL,
    provider character varying(50) NOT NULL,
    provider_user_id character varying(255),
    access_token text,
    refresh_token text,
    expires_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.oauth_sessions OWNER TO neondb_owner;

--
-- Name: oauth_sessions_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.oauth_sessions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.oauth_sessions_id_seq OWNER TO neondb_owner;

--
-- Name: oauth_sessions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.oauth_sessions_id_seq OWNED BY public.oauth_sessions.id;


--
-- Name: packing_lists; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.packing_lists (
    id integer NOT NULL,
    trip_id integer NOT NULL,
    user_id integer NOT NULL,
    item_name character varying(200) NOT NULL,
    category character varying(100),
    quantity integer DEFAULT 1,
    packed boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.packing_lists OWNER TO neondb_owner;

--
-- Name: packing_lists_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.packing_lists_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.packing_lists_id_seq OWNER TO neondb_owner;

--
-- Name: packing_lists_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.packing_lists_id_seq OWNED BY public.packing_lists.id;


--
-- Name: password_resets; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.password_resets (
    id integer NOT NULL,
    user_id integer NOT NULL,
    reset_token character varying(255) NOT NULL,
    expires_at timestamp without time zone NOT NULL,
    used boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.password_resets OWNER TO neondb_owner;

--
-- Name: password_resets_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.password_resets_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.password_resets_id_seq OWNER TO neondb_owner;

--
-- Name: password_resets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.password_resets_id_seq OWNED BY public.password_resets.id;


--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.personal_access_tokens (
    id integer NOT NULL,
    user_id integer NOT NULL,
    token_name character varying(100) NOT NULL,
    token character varying(255) NOT NULL,
    abilities text,
    last_used_at timestamp without time zone,
    expires_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.personal_access_tokens OWNER TO neondb_owner;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personal_access_tokens_id_seq OWNER TO neondb_owner;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: pomodoro_sessions; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.pomodoro_sessions (
    id integer NOT NULL,
    task_id integer,
    user_id integer NOT NULL,
    session_date date NOT NULL,
    start_time timestamp without time zone NOT NULL,
    end_time timestamp without time zone,
    duration_minutes integer DEFAULT 25,
    completed boolean DEFAULT false,
    interruptions integer DEFAULT 0,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.pomodoro_sessions OWNER TO neondb_owner;

--
-- Name: pomodoro_sessions_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.pomodoro_sessions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pomodoro_sessions_id_seq OWNER TO neondb_owner;

--
-- Name: pomodoro_sessions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.pomodoro_sessions_id_seq OWNED BY public.pomodoro_sessions.id;


--
-- Name: project_attachments; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.project_attachments (
    id integer NOT NULL,
    project_id integer,
    task_id integer,
    user_id integer NOT NULL,
    filename character varying(255) NOT NULL,
    file_path character varying(500) NOT NULL,
    file_size integer,
    file_type character varying(100),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.project_attachments OWNER TO neondb_owner;

--
-- Name: project_attachments_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.project_attachments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.project_attachments_id_seq OWNER TO neondb_owner;

--
-- Name: project_attachments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.project_attachments_id_seq OWNED BY public.project_attachments.id;


--
-- Name: project_checklists; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.project_checklists (
    id integer NOT NULL,
    task_id integer NOT NULL,
    item_text character varying(300) NOT NULL,
    is_completed boolean DEFAULT false,
    "position" integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.project_checklists OWNER TO neondb_owner;

--
-- Name: project_checklists_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.project_checklists_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.project_checklists_id_seq OWNER TO neondb_owner;

--
-- Name: project_checklists_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.project_checklists_id_seq OWNED BY public.project_checklists.id;


--
-- Name: project_tasks; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.project_tasks (
    id integer NOT NULL,
    project_id integer NOT NULL,
    user_id integer NOT NULL,
    title character varying(200) NOT NULL,
    description text,
    status character varying(50) DEFAULT 'todo'::character varying,
    priority character varying(50) DEFAULT 'medium'::character varying,
    assigned_to integer,
    due_date date,
    "position" integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.project_tasks OWNER TO neondb_owner;

--
-- Name: project_tasks_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.project_tasks_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.project_tasks_id_seq OWNER TO neondb_owner;

--
-- Name: project_tasks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.project_tasks_id_seq OWNED BY public.project_tasks.id;


--
-- Name: projects; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.projects (
    id integer NOT NULL,
    user_id integer NOT NULL,
    name character varying(200) NOT NULL,
    description text,
    status character varying(50) DEFAULT 'active'::character varying,
    priority character varying(50) DEFAULT 'medium'::character varying,
    start_date date,
    target_date date,
    progress integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.projects OWNER TO neondb_owner;

--
-- Name: projects_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.projects_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.projects_id_seq OWNER TO neondb_owner;

--
-- Name: projects_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.projects_id_seq OWNED BY public.projects.id;


--
-- Name: recipes; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.recipes (
    id integer NOT NULL,
    user_id integer,
    name character varying(255) NOT NULL,
    description text,
    category character varying(100),
    cuisine character varying(100),
    prep_time integer,
    cook_time integer,
    servings integer,
    ingredients text,
    instructions text,
    image_url text,
    source_url text,
    is_favorite boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.recipes OWNER TO neondb_owner;

--
-- Name: recipes_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.recipes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.recipes_id_seq OWNER TO neondb_owner;

--
-- Name: recipes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.recipes_id_seq OWNED BY public.recipes.id;


--
-- Name: relationship_interactions; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.relationship_interactions (
    id integer NOT NULL,
    relationship_id integer NOT NULL,
    user_id integer NOT NULL,
    interaction_type character varying(50),
    interaction_date date NOT NULL,
    mood_rating integer,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT relationship_interactions_mood_rating_check CHECK (((mood_rating >= 1) AND (mood_rating <= 10)))
);


ALTER TABLE public.relationship_interactions OWNER TO neondb_owner;

--
-- Name: relationship_interactions_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.relationship_interactions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.relationship_interactions_id_seq OWNER TO neondb_owner;

--
-- Name: relationship_interactions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.relationship_interactions_id_seq OWNED BY public.relationship_interactions.id;


--
-- Name: relationships; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.relationships (
    id integer NOT NULL,
    user_id integer NOT NULL,
    person_name character varying(150) NOT NULL,
    relationship_type character varying(100),
    start_date date,
    status character varying(50) DEFAULT 'active'::character varying,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.relationships OWNER TO neondb_owner;

--
-- Name: relationships_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.relationships_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.relationships_id_seq OWNER TO neondb_owner;

--
-- Name: relationships_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.relationships_id_seq OWNED BY public.relationships.id;


--
-- Name: resume_versions; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.resume_versions (
    id integer NOT NULL,
    user_id integer NOT NULL,
    version_name character varying(150) NOT NULL,
    file_path text,
    description text,
    is_current boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.resume_versions OWNER TO neondb_owner;

--
-- Name: resume_versions_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.resume_versions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.resume_versions_id_seq OWNER TO neondb_owner;

--
-- Name: resume_versions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.resume_versions_id_seq OWNED BY public.resume_versions.id;


--
-- Name: salary_progress; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.salary_progress (
    id integer NOT NULL,
    user_id integer NOT NULL,
    company_name character varying(200),
    "position" character varying(200),
    salary numeric(12,2) NOT NULL,
    currency character varying(10) DEFAULT 'USD'::character varying,
    effective_date date NOT NULL,
    salary_type character varying(50),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.salary_progress OWNER TO neondb_owner;

--
-- Name: salary_progress_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.salary_progress_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.salary_progress_id_seq OWNER TO neondb_owner;

--
-- Name: salary_progress_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.salary_progress_id_seq OWNED BY public.salary_progress.id;


--
-- Name: secure_notes; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.secure_notes (
    id integer NOT NULL,
    user_id integer NOT NULL,
    note_title character varying(200) NOT NULL,
    encrypted_content text NOT NULL,
    category character varying(100),
    tags text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.secure_notes OWNER TO neondb_owner;

--
-- Name: secure_notes_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.secure_notes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.secure_notes_id_seq OWNER TO neondb_owner;

--
-- Name: secure_notes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.secure_notes_id_seq OWNED BY public.secure_notes.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.sessions (
    id integer NOT NULL,
    user_id integer NOT NULL,
    session_id character varying(255) NOT NULL,
    data text,
    ip_address character varying(50),
    user_agent text,
    last_activity timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.sessions OWNER TO neondb_owner;

--
-- Name: sessions_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.sessions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sessions_id_seq OWNER TO neondb_owner;

--
-- Name: sessions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.sessions_id_seq OWNED BY public.sessions.id;


--
-- Name: shared_access; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.shared_access (
    id integer NOT NULL,
    shared_module_id integer NOT NULL,
    user_id integer NOT NULL,
    permission_level character varying(50) DEFAULT 'view'::character varying,
    invited_email character varying(200),
    invitation_status character varying(50) DEFAULT 'pending'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    accepted_at timestamp without time zone
);


ALTER TABLE public.shared_access OWNER TO neondb_owner;

--
-- Name: shared_access_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.shared_access_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.shared_access_id_seq OWNER TO neondb_owner;

--
-- Name: shared_access_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.shared_access_id_seq OWNED BY public.shared_access.id;


--
-- Name: shared_modules; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.shared_modules (
    id integer NOT NULL,
    owner_user_id integer NOT NULL,
    module_name character varying(100) NOT NULL,
    share_name character varying(200),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.shared_modules OWNER TO neondb_owner;

--
-- Name: shared_modules_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.shared_modules_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.shared_modules_id_seq OWNER TO neondb_owner;

--
-- Name: shared_modules_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.shared_modules_id_seq OWNED BY public.shared_modules.id;


--
-- Name: shopping_list_items; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.shopping_list_items (
    id integer NOT NULL,
    shopping_list_id integer NOT NULL,
    item_name character varying(200) NOT NULL,
    quantity character varying(50),
    category character varying(100),
    is_purchased boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.shopping_list_items OWNER TO neondb_owner;

--
-- Name: shopping_list_items_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.shopping_list_items_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.shopping_list_items_id_seq OWNER TO neondb_owner;

--
-- Name: shopping_list_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.shopping_list_items_id_seq OWNED BY public.shopping_list_items.id;


--
-- Name: shopping_lists; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.shopping_lists (
    id integer NOT NULL,
    user_id integer NOT NULL,
    name character varying(150) NOT NULL,
    week_start_date date,
    status character varying(50) DEFAULT 'active'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.shopping_lists OWNER TO neondb_owner;

--
-- Name: shopping_lists_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.shopping_lists_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.shopping_lists_id_seq OWNER TO neondb_owner;

--
-- Name: shopping_lists_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.shopping_lists_id_seq OWNED BY public.shopping_lists.id;


--
-- Name: sleep_logs; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.sleep_logs (
    id integer NOT NULL,
    user_id integer,
    date date NOT NULL,
    hours numeric(4,2),
    quality character varying(50),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.sleep_logs OWNER TO neondb_owner;

--
-- Name: sleep_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.sleep_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sleep_logs_id_seq OWNER TO neondb_owner;

--
-- Name: sleep_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.sleep_logs_id_seq OWNED BY public.sleep_logs.id;


--
-- Name: sleep_tracking; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.sleep_tracking (
    id integer NOT NULL,
    user_id integer NOT NULL,
    sleep_date date NOT NULL,
    bedtime timestamp without time zone,
    wake_time timestamp without time zone,
    total_sleep_hours numeric(4,2),
    deep_sleep_hours numeric(4,2),
    light_sleep_hours numeric(4,2),
    awake_time_hours numeric(4,2),
    quality_score integer,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT sleep_tracking_quality_score_check CHECK (((quality_score >= 1) AND (quality_score <= 100)))
);


ALTER TABLE public.sleep_tracking OWNER TO neondb_owner;

--
-- Name: sleep_tracking_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.sleep_tracking_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sleep_tracking_id_seq OWNER TO neondb_owner;

--
-- Name: sleep_tracking_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.sleep_tracking_id_seq OWNED BY public.sleep_tracking.id;


--
-- Name: smart_goals; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.smart_goals (
    id integer NOT NULL,
    user_id integer NOT NULL,
    goal_title character varying(200) NOT NULL,
    specific text,
    measurable text,
    achievable text,
    relevant text,
    time_bound date,
    status character varying(50) DEFAULT 'in_progress'::character varying,
    progress_percentage integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.smart_goals OWNER TO neondb_owner;

--
-- Name: smart_goals_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.smart_goals_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.smart_goals_id_seq OWNER TO neondb_owner;

--
-- Name: smart_goals_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.smart_goals_id_seq OWNED BY public.smart_goals.id;


--
-- Name: stress_logs; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.stress_logs (
    id integer NOT NULL,
    user_id integer NOT NULL,
    log_date date NOT NULL,
    stress_level integer NOT NULL,
    triggers text,
    coping_strategies text,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.stress_logs OWNER TO neondb_owner;

--
-- Name: stress_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.stress_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.stress_logs_id_seq OWNER TO neondb_owner;

--
-- Name: stress_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.stress_logs_id_seq OWNED BY public.stress_logs.id;


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
-- Name: subscriptions_advanced; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.subscriptions_advanced (
    id integer NOT NULL,
    user_id integer NOT NULL,
    service_name character varying(200) NOT NULL,
    category character varying(100),
    amount numeric(10,2) NOT NULL,
    billing_cycle character varying(50) NOT NULL,
    start_date date NOT NULL,
    next_billing_date date NOT NULL,
    auto_renew boolean DEFAULT true,
    payment_method character varying(100),
    subscription_url text,
    cancellation_reminder_days integer DEFAULT 7,
    status character varying(50) DEFAULT 'active'::character varying,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.subscriptions_advanced OWNER TO neondb_owner;

--
-- Name: subscriptions_advanced_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.subscriptions_advanced_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.subscriptions_advanced_id_seq OWNER TO neondb_owner;

--
-- Name: subscriptions_advanced_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.subscriptions_advanced_id_seq OWNED BY public.subscriptions_advanced.id;


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
-- Name: symptom_logs; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.symptom_logs (
    id integer NOT NULL,
    symptom_id integer NOT NULL,
    user_id integer NOT NULL,
    severity integer,
    occurred_at timestamp without time zone NOT NULL,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT symptom_logs_severity_check CHECK (((severity >= 1) AND (severity <= 10)))
);


ALTER TABLE public.symptom_logs OWNER TO neondb_owner;

--
-- Name: symptom_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.symptom_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.symptom_logs_id_seq OWNER TO neondb_owner;

--
-- Name: symptom_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.symptom_logs_id_seq OWNED BY public.symptom_logs.id;


--
-- Name: symptoms; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.symptoms (
    id integer NOT NULL,
    user_id integer NOT NULL,
    symptom_name character varying(150) NOT NULL,
    severity integer,
    body_part character varying(100),
    description text,
    occurred_at timestamp without time zone NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT symptoms_severity_check CHECK (((severity >= 1) AND (severity <= 10)))
);


ALTER TABLE public.symptoms OWNER TO neondb_owner;

--
-- Name: symptoms_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.symptoms_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.symptoms_id_seq OWNER TO neondb_owner;

--
-- Name: symptoms_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.symptoms_id_seq OWNED BY public.symptoms.id;


--
-- Name: task_dependencies; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.task_dependencies (
    id integer NOT NULL,
    task_id integer NOT NULL,
    depends_on_task_id integer NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.task_dependencies OWNER TO neondb_owner;

--
-- Name: task_dependencies_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.task_dependencies_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.task_dependencies_id_seq OWNER TO neondb_owner;

--
-- Name: task_dependencies_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.task_dependencies_id_seq OWNED BY public.task_dependencies.id;


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
-- Name: tax_documents; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.tax_documents (
    id integer NOT NULL,
    user_id integer NOT NULL,
    document_name character varying(250) NOT NULL,
    tax_year integer NOT NULL,
    category character varying(100),
    amount numeric(12,2),
    file_path text,
    document_date date,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.tax_documents OWNER TO neondb_owner;

--
-- Name: tax_documents_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.tax_documents_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tax_documents_id_seq OWNER TO neondb_owner;

--
-- Name: tax_documents_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.tax_documents_id_seq OWNED BY public.tax_documents.id;


--
-- Name: team_board_members; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.team_board_members (
    id integer NOT NULL,
    board_id integer,
    user_id integer,
    role character varying(50) DEFAULT 'member'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.team_board_members OWNER TO neondb_owner;

--
-- Name: team_board_members_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.team_board_members_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.team_board_members_id_seq OWNER TO neondb_owner;

--
-- Name: team_board_members_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.team_board_members_id_seq OWNED BY public.team_board_members.id;


--
-- Name: team_boards; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.team_boards (
    id integer NOT NULL,
    owner_id integer,
    name character varying(255) NOT NULL,
    description text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.team_boards OWNER TO neondb_owner;

--
-- Name: team_boards_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.team_boards_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.team_boards_id_seq OWNER TO neondb_owner;

--
-- Name: team_boards_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.team_boards_id_seq OWNED BY public.team_boards.id;


--
-- Name: team_tasks; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.team_tasks (
    id integer NOT NULL,
    board_id integer,
    assigned_to integer,
    title character varying(255) NOT NULL,
    description text,
    status character varying(50) DEFAULT 'todo'::character varying,
    priority character varying(50),
    due_date date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.team_tasks OWNER TO neondb_owner;

--
-- Name: team_tasks_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.team_tasks_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.team_tasks_id_seq OWNER TO neondb_owner;

--
-- Name: team_tasks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.team_tasks_id_seq OWNED BY public.team_tasks.id;


--
-- Name: time_logs; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.time_logs (
    id integer NOT NULL,
    project_id integer,
    user_id integer NOT NULL,
    activity_description character varying(255),
    hours_worked numeric(5,2) NOT NULL,
    log_date date NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.time_logs OWNER TO neondb_owner;

--
-- Name: time_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.time_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.time_logs_id_seq OWNER TO neondb_owner;

--
-- Name: time_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.time_logs_id_seq OWNED BY public.time_logs.id;


--
-- Name: travel_journal; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.travel_journal (
    id integer NOT NULL,
    trip_id integer,
    user_id integer NOT NULL,
    entry_date date NOT NULL,
    entry_title character varying(200),
    entry_content text NOT NULL,
    location character varying(200),
    mood character varying(50),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.travel_journal OWNER TO neondb_owner;

--
-- Name: travel_journal_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.travel_journal_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.travel_journal_id_seq OWNER TO neondb_owner;

--
-- Name: travel_journal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.travel_journal_id_seq OWNED BY public.travel_journal.id;


--
-- Name: trip_itinerary; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.trip_itinerary (
    id integer NOT NULL,
    trip_id integer NOT NULL,
    user_id integer NOT NULL,
    day_number integer,
    activity_name character varying(200) NOT NULL,
    start_time time without time zone,
    end_time time without time zone,
    location character varying(200),
    cost numeric(10,2),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.trip_itinerary OWNER TO neondb_owner;

--
-- Name: trip_itinerary_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.trip_itinerary_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.trip_itinerary_id_seq OWNER TO neondb_owner;

--
-- Name: trip_itinerary_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.trip_itinerary_id_seq OWNED BY public.trip_itinerary.id;


--
-- Name: trips; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.trips (
    id integer NOT NULL,
    user_id integer NOT NULL,
    trip_name character varying(200) NOT NULL,
    destination character varying(200) NOT NULL,
    start_date date NOT NULL,
    end_date date NOT NULL,
    budget numeric(12,2),
    trip_type character varying(100),
    status character varying(50) DEFAULT 'planning'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.trips OWNER TO neondb_owner;

--
-- Name: trips_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.trips_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.trips_id_seq OWNER TO neondb_owner;

--
-- Name: trips_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.trips_id_seq OWNED BY public.trips.id;


--
-- Name: user_devices; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.user_devices (
    id integer NOT NULL,
    user_id integer NOT NULL,
    device_name character varying(150) NOT NULL,
    device_type character varying(50),
    browser character varying(100),
    ip_address character varying(50),
    last_accessed timestamp without time zone,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.user_devices OWNER TO neondb_owner;

--
-- Name: user_devices_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.user_devices_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.user_devices_id_seq OWNER TO neondb_owner;

--
-- Name: user_devices_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.user_devices_id_seq OWNED BY public.user_devices.id;


--
-- Name: user_sessions; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.user_sessions (
    id integer NOT NULL,
    user_id integer NOT NULL,
    device_id integer,
    session_token character varying(255) NOT NULL,
    ip_address character varying(50),
    user_agent text,
    expires_at timestamp without time zone NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.user_sessions OWNER TO neondb_owner;

--
-- Name: user_sessions_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.user_sessions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.user_sessions_id_seq OWNER TO neondb_owner;

--
-- Name: user_sessions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.user_sessions_id_seq OWNED BY public.user_sessions.id;


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
-- Name: vault_items; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.vault_items (
    id integer NOT NULL,
    user_id integer NOT NULL,
    item_name character varying(200) NOT NULL,
    item_type character varying(50) NOT NULL,
    encrypted_data text NOT NULL,
    category character varying(100),
    tags text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.vault_items OWNER TO neondb_owner;

--
-- Name: vault_items_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.vault_items_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vault_items_id_seq OWNER TO neondb_owner;

--
-- Name: vault_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.vault_items_id_seq OWNED BY public.vault_items.id;


--
-- Name: vehicle_maintenance; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.vehicle_maintenance (
    id integer NOT NULL,
    vehicle_id integer NOT NULL,
    user_id integer NOT NULL,
    maintenance_type character varying(100),
    maintenance_date date NOT NULL,
    mileage integer,
    cost numeric(10,2),
    provider character varying(150),
    notes text,
    next_service_date date,
    next_service_mileage integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.vehicle_maintenance OWNER TO neondb_owner;

--
-- Name: vehicle_maintenance_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.vehicle_maintenance_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicle_maintenance_id_seq OWNER TO neondb_owner;

--
-- Name: vehicle_maintenance_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.vehicle_maintenance_id_seq OWNED BY public.vehicle_maintenance.id;


--
-- Name: vehicles; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.vehicles (
    id integer NOT NULL,
    user_id integer NOT NULL,
    vehicle_name character varying(150) NOT NULL,
    make character varying(100),
    model character varying(100),
    year integer,
    vin character varying(50),
    license_plate character varying(20),
    purchase_date date,
    purchase_price numeric(12,2),
    current_mileage integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.vehicles OWNER TO neondb_owner;

--
-- Name: vehicles_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.vehicles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicles_id_seq OWNER TO neondb_owner;

--
-- Name: vehicles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.vehicles_id_seq OWNED BY public.vehicles.id;


--
-- Name: water_goals; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.water_goals (
    id integer NOT NULL,
    user_id integer NOT NULL,
    daily_goal_ml integer NOT NULL,
    start_date date NOT NULL,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.water_goals OWNER TO neondb_owner;

--
-- Name: water_goals_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.water_goals_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.water_goals_id_seq OWNER TO neondb_owner;

--
-- Name: water_goals_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.water_goals_id_seq OWNED BY public.water_goals.id;


--
-- Name: water_intake; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.water_intake (
    id integer NOT NULL,
    user_id integer,
    date date NOT NULL,
    amount_ml integer NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.water_intake OWNER TO neondb_owner;

--
-- Name: water_intake_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.water_intake_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.water_intake_id_seq OWNER TO neondb_owner;

--
-- Name: water_intake_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.water_intake_id_seq OWNED BY public.water_intake.id;


--
-- Name: accounts id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.accounts ALTER COLUMN id SET DEFAULT nextval('public.accounts_id_seq'::regclass);


--
-- Name: activity_logs id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.activity_logs ALTER COLUMN id SET DEFAULT nextval('public.activity_logs_id_seq'::regclass);


--
-- Name: ai_briefings id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_briefings ALTER COLUMN id SET DEFAULT nextval('public.ai_briefings_id_seq'::regclass);


--
-- Name: ai_chat_contexts id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_chat_contexts ALTER COLUMN id SET DEFAULT nextval('public.ai_chat_contexts_id_seq'::regclass);


--
-- Name: ai_conversations id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_conversations ALTER COLUMN id SET DEFAULT nextval('public.ai_conversations_id_seq'::regclass);


--
-- Name: ai_daily_briefings_v2 id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_daily_briefings_v2 ALTER COLUMN id SET DEFAULT nextval('public.ai_daily_briefings_v2_id_seq'::regclass);


--
-- Name: ai_messages id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_messages ALTER COLUMN id SET DEFAULT nextval('public.ai_messages_id_seq'::regclass);


--
-- Name: ai_module_connections id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_module_connections ALTER COLUMN id SET DEFAULT nextval('public.ai_module_connections_id_seq'::regclass);


--
-- Name: ai_reports id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_reports ALTER COLUMN id SET DEFAULT nextval('public.ai_reports_id_seq'::regclass);


--
-- Name: ai_weekly_summaries id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_weekly_summaries ALTER COLUMN id SET DEFAULT nextval('public.ai_weekly_summaries_id_seq'::regclass);


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
-- Name: bill_payments id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.bill_payments ALTER COLUMN id SET DEFAULT nextval('public.bill_payments_id_seq'::regclass);


--
-- Name: bills id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.bills ALTER COLUMN id SET DEFAULT nextval('public.bills_id_seq'::regclass);


--
-- Name: birthdays id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.birthdays ALTER COLUMN id SET DEFAULT nextval('public.birthdays_id_seq'::regclass);


--
-- Name: books id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.books ALTER COLUMN id SET DEFAULT nextval('public.books_id_seq'::regclass);


--
-- Name: breathing_exercises id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.breathing_exercises ALTER COLUMN id SET DEFAULT nextval('public.breathing_exercises_id_seq'::regclass);


--
-- Name: budget_envelopes id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.budget_envelopes ALTER COLUMN id SET DEFAULT nextval('public.budget_envelopes_id_seq'::regclass);


--
-- Name: budget_transactions id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.budget_transactions ALTER COLUMN id SET DEFAULT nextval('public.budget_transactions_id_seq'::regclass);


--
-- Name: budgets id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.budgets ALTER COLUMN id SET DEFAULT nextval('public.budgets_id_seq'::regclass);


--
-- Name: calendar_connections id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.calendar_connections ALTER COLUMN id SET DEFAULT nextval('public.calendar_connections_id_seq'::regclass);


--
-- Name: calendar_events id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.calendar_events ALTER COLUMN id SET DEFAULT nextval('public.calendar_events_id_seq'::regclass);


--
-- Name: calendar_sync_logs id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.calendar_sync_logs ALTER COLUMN id SET DEFAULT nextval('public.calendar_sync_logs_id_seq'::regclass);


--
-- Name: calendar_sync_settings id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.calendar_sync_settings ALTER COLUMN id SET DEFAULT nextval('public.calendar_sync_settings_id_seq'::regclass);


--
-- Name: career_certifications id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.career_certifications ALTER COLUMN id SET DEFAULT nextval('public.career_certifications_id_seq'::regclass);


--
-- Name: career_projects id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.career_projects ALTER COLUMN id SET DEFAULT nextval('public.career_projects_id_seq'::regclass);


--
-- Name: career_tasks id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.career_tasks ALTER COLUMN id SET DEFAULT nextval('public.career_tasks_id_seq'::regclass);


--
-- Name: chat_messages id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.chat_messages ALTER COLUMN id SET DEFAULT nextval('public.chat_messages_id_seq'::regclass);


--
-- Name: chat_sessions id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.chat_sessions ALTER COLUMN id SET DEFAULT nextval('public.chat_sessions_id_seq'::regclass);


--
-- Name: cloud_backups id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.cloud_backups ALTER COLUMN id SET DEFAULT nextval('public.cloud_backups_id_seq'::regclass);


--
-- Name: contact_interactions id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.contact_interactions ALTER COLUMN id SET DEFAULT nextval('public.contact_interactions_id_seq'::regclass);


--
-- Name: contact_reminders id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.contact_reminders ALTER COLUMN id SET DEFAULT nextval('public.contact_reminders_id_seq'::regclass);


--
-- Name: contacts id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.contacts ALTER COLUMN id SET DEFAULT nextval('public.contacts_id_seq'::regclass);


--
-- Name: courses id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.courses ALTER COLUMN id SET DEFAULT nextval('public.courses_id_seq'::regclass);


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
-- Name: data_export_logs id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.data_export_logs ALTER COLUMN id SET DEFAULT nextval('public.data_export_logs_id_seq'::regclass);


--
-- Name: debt_payments id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.debt_payments ALTER COLUMN id SET DEFAULT nextval('public.debt_payments_id_seq'::regclass);


--
-- Name: debts id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.debts ALTER COLUMN id SET DEFAULT nextval('public.debts_id_seq'::regclass);


--
-- Name: diet_meals id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.diet_meals ALTER COLUMN id SET DEFAULT nextval('public.diet_meals_id_seq'::regclass);


--
-- Name: diet_plans id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.diet_plans ALTER COLUMN id SET DEFAULT nextval('public.diet_plans_id_seq'::regclass);


--
-- Name: document_summaries id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.document_summaries ALTER COLUMN id SET DEFAULT nextval('public.document_summaries_id_seq'::regclass);


--
-- Name: document_versions id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.document_versions ALTER COLUMN id SET DEFAULT nextval('public.document_versions_id_seq'::regclass);


--
-- Name: documents id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.documents ALTER COLUMN id SET DEFAULT nextval('public.documents_id_seq'::regclass);


--
-- Name: emergency_contacts id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.emergency_contacts ALTER COLUMN id SET DEFAULT nextval('public.emergency_contacts_id_seq'::regclass);


--
-- Name: emergency_log id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.emergency_log ALTER COLUMN id SET DEFAULT nextval('public.emergency_log_id_seq'::regclass);


--
-- Name: encrypted_notes id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.encrypted_notes ALTER COLUMN id SET DEFAULT nextval('public.encrypted_notes_id_seq'::regclass);


--
-- Name: event_budget_items id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.event_budget_items ALTER COLUMN id SET DEFAULT nextval('public.event_budget_items_id_seq'::regclass);


--
-- Name: event_checklists id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.event_checklists ALTER COLUMN id SET DEFAULT nextval('public.event_checklists_id_seq'::regclass);


--
-- Name: event_guests id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.event_guests ALTER COLUMN id SET DEFAULT nextval('public.event_guests_id_seq'::regclass);


--
-- Name: events id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.events ALTER COLUMN id SET DEFAULT nextval('public.events_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: family_members id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.family_members ALTER COLUMN id SET DEFAULT nextval('public.family_members_id_seq'::regclass);


--
-- Name: finance id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.finance ALTER COLUMN id SET DEFAULT nextval('public.finance_id_seq'::regclass);


--
-- Name: financial_accounts id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.financial_accounts ALTER COLUMN id SET DEFAULT nextval('public.financial_accounts_id_seq'::regclass);


--
-- Name: financial_forecasts id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.financial_forecasts ALTER COLUMN id SET DEFAULT nextval('public.financial_forecasts_id_seq'::regclass);


--
-- Name: financial_projections id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.financial_projections ALTER COLUMN id SET DEFAULT nextval('public.financial_projections_id_seq'::regclass);


--
-- Name: flashcards id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.flashcards ALTER COLUMN id SET DEFAULT nextval('public.flashcards_id_seq'::regclass);


--
-- Name: freelance_clients id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.freelance_clients ALTER COLUMN id SET DEFAULT nextval('public.freelance_clients_id_seq'::regclass);


--
-- Name: freelance_invoices id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.freelance_invoices ALTER COLUMN id SET DEFAULT nextval('public.freelance_invoices_id_seq'::regclass);


--
-- Name: freelance_projects id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.freelance_projects ALTER COLUMN id SET DEFAULT nextval('public.freelance_projects_id_seq'::regclass);


--
-- Name: gifts id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.gifts ALTER COLUMN id SET DEFAULT nextval('public.gifts_id_seq'::regclass);


--
-- Name: goal_activities id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.goal_activities ALTER COLUMN id SET DEFAULT nextval('public.goal_activities_id_seq'::regclass);


--
-- Name: goals id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.goals ALTER COLUMN id SET DEFAULT nextval('public.goals_id_seq'::regclass);


--
-- Name: grocery_items id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.grocery_items ALTER COLUMN id SET DEFAULT nextval('public.grocery_items_id_seq'::regclass);


--
-- Name: grocery_lists id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.grocery_lists ALTER COLUMN id SET DEFAULT nextval('public.grocery_lists_id_seq'::regclass);


--
-- Name: gym_exercises id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.gym_exercises ALTER COLUMN id SET DEFAULT nextval('public.gym_exercises_id_seq'::regclass);


--
-- Name: gym_routines id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.gym_routines ALTER COLUMN id SET DEFAULT nextval('public.gym_routines_id_seq'::regclass);


--
-- Name: gym_sessions id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.gym_sessions ALTER COLUMN id SET DEFAULT nextval('public.gym_sessions_id_seq'::regclass);


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
-- Name: health_profiles id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.health_profiles ALTER COLUMN id SET DEFAULT nextval('public.health_profiles_id_seq'::regclass);


--
-- Name: hobbies id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.hobbies ALTER COLUMN id SET DEFAULT nextval('public.hobbies_id_seq'::regclass);


--
-- Name: home_assets id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.home_assets ALTER COLUMN id SET DEFAULT nextval('public.home_assets_id_seq'::regclass);


--
-- Name: household_expense_shares id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.household_expense_shares ALTER COLUMN id SET DEFAULT nextval('public.household_expense_shares_id_seq'::regclass);


--
-- Name: household_expenses id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.household_expenses ALTER COLUMN id SET DEFAULT nextval('public.household_expenses_id_seq'::regclass);


--
-- Name: household_tasks id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.household_tasks ALTER COLUMN id SET DEFAULT nextval('public.household_tasks_id_seq'::regclass);


--
-- Name: interviews id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.interviews ALTER COLUMN id SET DEFAULT nextval('public.interviews_id_seq'::regclass);


--
-- Name: investment_portfolio id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.investment_portfolio ALTER COLUMN id SET DEFAULT nextval('public.investment_portfolio_id_seq'::regclass);


--
-- Name: investments id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.investments ALTER COLUMN id SET DEFAULT nextval('public.investments_id_seq'::regclass);


--
-- Name: job_applications id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.job_applications ALTER COLUMN id SET DEFAULT nextval('public.job_applications_id_seq'::regclass);


--
-- Name: job_batches id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.job_batches ALTER COLUMN id SET DEFAULT nextval('public.job_batches_id_seq'::regclass);


--
-- Name: job_logs id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.job_logs ALTER COLUMN id SET DEFAULT nextval('public.job_logs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: journal id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.journal ALTER COLUMN id SET DEFAULT nextval('public.journal_id_seq'::regclass);


--
-- Name: knowledge_items id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.knowledge_items ALTER COLUMN id SET DEFAULT nextval('public.knowledge_items_id_seq'::regclass);


--
-- Name: learning id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.learning ALTER COLUMN id SET DEFAULT nextval('public.learning_id_seq'::regclass);


--
-- Name: learning_courses id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.learning_courses ALTER COLUMN id SET DEFAULT nextval('public.learning_courses_id_seq'::regclass);


--
-- Name: learning_notes id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.learning_notes ALTER COLUMN id SET DEFAULT nextval('public.learning_notes_id_seq'::regclass);


--
-- Name: life_advisor_actions id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.life_advisor_actions ALTER COLUMN id SET DEFAULT nextval('public.life_advisor_actions_id_seq'::regclass);


--
-- Name: life_advisor_briefings id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.life_advisor_briefings ALTER COLUMN id SET DEFAULT nextval('public.life_advisor_briefings_id_seq'::regclass);


--
-- Name: life_area_metrics id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.life_area_metrics ALTER COLUMN id SET DEFAULT nextval('public.life_area_metrics_id_seq'::regclass);


--
-- Name: life_balance_logs id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.life_balance_logs ALTER COLUMN id SET DEFAULT nextval('public.life_balance_logs_id_seq'::regclass);


--
-- Name: life_event_predictions id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.life_event_predictions ALTER COLUMN id SET DEFAULT nextval('public.life_event_predictions_id_seq'::regclass);


--
-- Name: maintenance_logs id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.maintenance_logs ALTER COLUMN id SET DEFAULT nextval('public.maintenance_logs_id_seq'::regclass);


--
-- Name: meal_plans id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans ALTER COLUMN id SET DEFAULT nextval('public.meal_plans_id_seq'::regclass);


--
-- Name: media id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.media ALTER COLUMN id SET DEFAULT nextval('public.media_id_seq'::regclass);


--
-- Name: medical_records id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.medical_records ALTER COLUMN id SET DEFAULT nextval('public.medical_records_id_seq'::regclass);


--
-- Name: medication_logs id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.medication_logs ALTER COLUMN id SET DEFAULT nextval('public.medication_logs_id_seq'::regclass);


--
-- Name: medications id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.medications ALTER COLUMN id SET DEFAULT nextval('public.medications_id_seq'::regclass);


--
-- Name: meditation_sessions id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meditation_sessions ALTER COLUMN id SET DEFAULT nextval('public.meditation_sessions_id_seq'::regclass);


--
-- Name: mood_entries id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.mood_entries ALTER COLUMN id SET DEFAULT nextval('public.mood_entries_id_seq'::regclass);


--
-- Name: note_categories id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.note_categories ALTER COLUMN id SET DEFAULT nextval('public.note_categories_id_seq'::regclass);


--
-- Name: notes id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.notes ALTER COLUMN id SET DEFAULT nextval('public.notes_id_seq'::regclass);


--
-- Name: notifications id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.notifications ALTER COLUMN id SET DEFAULT nextval('public.notifications_id_seq'::regclass);


--
-- Name: oauth_sessions id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.oauth_sessions ALTER COLUMN id SET DEFAULT nextval('public.oauth_sessions_id_seq'::regclass);


--
-- Name: packing_lists id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.packing_lists ALTER COLUMN id SET DEFAULT nextval('public.packing_lists_id_seq'::regclass);


--
-- Name: password_resets id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.password_resets ALTER COLUMN id SET DEFAULT nextval('public.password_resets_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: pomodoro_sessions id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.pomodoro_sessions ALTER COLUMN id SET DEFAULT nextval('public.pomodoro_sessions_id_seq'::regclass);


--
-- Name: project_attachments id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.project_attachments ALTER COLUMN id SET DEFAULT nextval('public.project_attachments_id_seq'::regclass);


--
-- Name: project_checklists id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.project_checklists ALTER COLUMN id SET DEFAULT nextval('public.project_checklists_id_seq'::regclass);


--
-- Name: project_tasks id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.project_tasks ALTER COLUMN id SET DEFAULT nextval('public.project_tasks_id_seq'::regclass);


--
-- Name: projects id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.projects ALTER COLUMN id SET DEFAULT nextval('public.projects_id_seq'::regclass);


--
-- Name: recipes id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.recipes ALTER COLUMN id SET DEFAULT nextval('public.recipes_id_seq'::regclass);


--
-- Name: relationship_interactions id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.relationship_interactions ALTER COLUMN id SET DEFAULT nextval('public.relationship_interactions_id_seq'::regclass);


--
-- Name: relationships id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.relationships ALTER COLUMN id SET DEFAULT nextval('public.relationships_id_seq'::regclass);


--
-- Name: resume_versions id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.resume_versions ALTER COLUMN id SET DEFAULT nextval('public.resume_versions_id_seq'::regclass);


--
-- Name: salary_progress id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.salary_progress ALTER COLUMN id SET DEFAULT nextval('public.salary_progress_id_seq'::regclass);


--
-- Name: secure_notes id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.secure_notes ALTER COLUMN id SET DEFAULT nextval('public.secure_notes_id_seq'::regclass);


--
-- Name: sessions id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.sessions ALTER COLUMN id SET DEFAULT nextval('public.sessions_id_seq'::regclass);


--
-- Name: shared_access id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.shared_access ALTER COLUMN id SET DEFAULT nextval('public.shared_access_id_seq'::regclass);


--
-- Name: shared_modules id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.shared_modules ALTER COLUMN id SET DEFAULT nextval('public.shared_modules_id_seq'::regclass);


--
-- Name: shopping_list_items id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.shopping_list_items ALTER COLUMN id SET DEFAULT nextval('public.shopping_list_items_id_seq'::regclass);


--
-- Name: shopping_lists id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.shopping_lists ALTER COLUMN id SET DEFAULT nextval('public.shopping_lists_id_seq'::regclass);


--
-- Name: sleep_logs id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.sleep_logs ALTER COLUMN id SET DEFAULT nextval('public.sleep_logs_id_seq'::regclass);


--
-- Name: sleep_tracking id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.sleep_tracking ALTER COLUMN id SET DEFAULT nextval('public.sleep_tracking_id_seq'::regclass);


--
-- Name: smart_goals id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.smart_goals ALTER COLUMN id SET DEFAULT nextval('public.smart_goals_id_seq'::regclass);


--
-- Name: stress_logs id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.stress_logs ALTER COLUMN id SET DEFAULT nextval('public.stress_logs_id_seq'::regclass);


--
-- Name: subscriptions id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.subscriptions ALTER COLUMN id SET DEFAULT nextval('public.subscriptions_id_seq'::regclass);


--
-- Name: subscriptions_advanced id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.subscriptions_advanced ALTER COLUMN id SET DEFAULT nextval('public.subscriptions_advanced_id_seq'::regclass);


--
-- Name: symptom_logs id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.symptom_logs ALTER COLUMN id SET DEFAULT nextval('public.symptom_logs_id_seq'::regclass);


--
-- Name: symptoms id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.symptoms ALTER COLUMN id SET DEFAULT nextval('public.symptoms_id_seq'::regclass);


--
-- Name: task_dependencies id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.task_dependencies ALTER COLUMN id SET DEFAULT nextval('public.task_dependencies_id_seq'::regclass);


--
-- Name: tasks id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.tasks ALTER COLUMN id SET DEFAULT nextval('public.tasks_id_seq'::regclass);


--
-- Name: tax_documents id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.tax_documents ALTER COLUMN id SET DEFAULT nextval('public.tax_documents_id_seq'::regclass);


--
-- Name: team_board_members id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.team_board_members ALTER COLUMN id SET DEFAULT nextval('public.team_board_members_id_seq'::regclass);


--
-- Name: team_boards id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.team_boards ALTER COLUMN id SET DEFAULT nextval('public.team_boards_id_seq'::regclass);


--
-- Name: team_tasks id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.team_tasks ALTER COLUMN id SET DEFAULT nextval('public.team_tasks_id_seq'::regclass);


--
-- Name: time_logs id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.time_logs ALTER COLUMN id SET DEFAULT nextval('public.time_logs_id_seq'::regclass);


--
-- Name: travel_journal id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.travel_journal ALTER COLUMN id SET DEFAULT nextval('public.travel_journal_id_seq'::regclass);


--
-- Name: trip_itinerary id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.trip_itinerary ALTER COLUMN id SET DEFAULT nextval('public.trip_itinerary_id_seq'::regclass);


--
-- Name: trips id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.trips ALTER COLUMN id SET DEFAULT nextval('public.trips_id_seq'::regclass);


--
-- Name: user_devices id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.user_devices ALTER COLUMN id SET DEFAULT nextval('public.user_devices_id_seq'::regclass);


--
-- Name: user_sessions id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.user_sessions ALTER COLUMN id SET DEFAULT nextval('public.user_sessions_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: vault_items id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.vault_items ALTER COLUMN id SET DEFAULT nextval('public.vault_items_id_seq'::regclass);


--
-- Name: vehicle_maintenance id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.vehicle_maintenance ALTER COLUMN id SET DEFAULT nextval('public.vehicle_maintenance_id_seq'::regclass);


--
-- Name: vehicles id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.vehicles ALTER COLUMN id SET DEFAULT nextval('public.vehicles_id_seq'::regclass);


--
-- Name: water_goals id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.water_goals ALTER COLUMN id SET DEFAULT nextval('public.water_goals_id_seq'::regclass);


--
-- Name: water_intake id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.water_intake ALTER COLUMN id SET DEFAULT nextval('public.water_intake_id_seq'::regclass);


--
-- Name: accounts accounts_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.accounts
    ADD CONSTRAINT accounts_pkey PRIMARY KEY (id);


--
-- Name: activity_logs activity_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.activity_logs
    ADD CONSTRAINT activity_logs_pkey PRIMARY KEY (id);


--
-- Name: ai_briefings ai_briefings_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_briefings
    ADD CONSTRAINT ai_briefings_pkey PRIMARY KEY (id);


--
-- Name: ai_chat_contexts ai_chat_contexts_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_chat_contexts
    ADD CONSTRAINT ai_chat_contexts_pkey PRIMARY KEY (id);


--
-- Name: ai_conversations ai_conversations_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_conversations
    ADD CONSTRAINT ai_conversations_pkey PRIMARY KEY (id);


--
-- Name: ai_daily_briefings_v2 ai_daily_briefings_v2_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_daily_briefings_v2
    ADD CONSTRAINT ai_daily_briefings_v2_pkey PRIMARY KEY (id);


--
-- Name: ai_messages ai_messages_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_messages
    ADD CONSTRAINT ai_messages_pkey PRIMARY KEY (id);


--
-- Name: ai_module_connections ai_module_connections_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_module_connections
    ADD CONSTRAINT ai_module_connections_pkey PRIMARY KEY (id);


--
-- Name: ai_reports ai_reports_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_reports
    ADD CONSTRAINT ai_reports_pkey PRIMARY KEY (id);


--
-- Name: ai_weekly_summaries ai_weekly_summaries_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_weekly_summaries
    ADD CONSTRAINT ai_weekly_summaries_pkey PRIMARY KEY (id);


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
-- Name: bill_payments bill_payments_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.bill_payments
    ADD CONSTRAINT bill_payments_pkey PRIMARY KEY (id);


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
-- Name: books books_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.books
    ADD CONSTRAINT books_pkey PRIMARY KEY (id);


--
-- Name: breathing_exercises breathing_exercises_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.breathing_exercises
    ADD CONSTRAINT breathing_exercises_pkey PRIMARY KEY (id);


--
-- Name: budget_envelopes budget_envelopes_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.budget_envelopes
    ADD CONSTRAINT budget_envelopes_pkey PRIMARY KEY (id);


--
-- Name: budget_transactions budget_transactions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.budget_transactions
    ADD CONSTRAINT budget_transactions_pkey PRIMARY KEY (id);


--
-- Name: budgets budgets_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.budgets
    ADD CONSTRAINT budgets_pkey PRIMARY KEY (id);


--
-- Name: calendar_connections calendar_connections_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.calendar_connections
    ADD CONSTRAINT calendar_connections_pkey PRIMARY KEY (id);


--
-- Name: calendar_events calendar_events_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.calendar_events
    ADD CONSTRAINT calendar_events_pkey PRIMARY KEY (id);


--
-- Name: calendar_sync_logs calendar_sync_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.calendar_sync_logs
    ADD CONSTRAINT calendar_sync_logs_pkey PRIMARY KEY (id);


--
-- Name: calendar_sync_settings calendar_sync_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.calendar_sync_settings
    ADD CONSTRAINT calendar_sync_settings_pkey PRIMARY KEY (id);


--
-- Name: calendar_sync_settings calendar_sync_settings_user_id_provider_calendar_id_key; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.calendar_sync_settings
    ADD CONSTRAINT calendar_sync_settings_user_id_provider_calendar_id_key UNIQUE (user_id, provider, calendar_id);


--
-- Name: career_certifications career_certifications_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.career_certifications
    ADD CONSTRAINT career_certifications_pkey PRIMARY KEY (id);


--
-- Name: career_projects career_projects_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.career_projects
    ADD CONSTRAINT career_projects_pkey PRIMARY KEY (id);


--
-- Name: career_tasks career_tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.career_tasks
    ADD CONSTRAINT career_tasks_pkey PRIMARY KEY (id);


--
-- Name: chat_messages chat_messages_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.chat_messages
    ADD CONSTRAINT chat_messages_pkey PRIMARY KEY (id);


--
-- Name: chat_sessions chat_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.chat_sessions
    ADD CONSTRAINT chat_sessions_pkey PRIMARY KEY (id);


--
-- Name: cloud_backups cloud_backups_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.cloud_backups
    ADD CONSTRAINT cloud_backups_pkey PRIMARY KEY (id);


--
-- Name: contact_interactions contact_interactions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.contact_interactions
    ADD CONSTRAINT contact_interactions_pkey PRIMARY KEY (id);


--
-- Name: contact_reminders contact_reminders_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.contact_reminders
    ADD CONSTRAINT contact_reminders_pkey PRIMARY KEY (id);


--
-- Name: contacts contacts_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.contacts
    ADD CONSTRAINT contacts_pkey PRIMARY KEY (id);


--
-- Name: courses courses_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.courses
    ADD CONSTRAINT courses_pkey PRIMARY KEY (id);


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
-- Name: data_export_logs data_export_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.data_export_logs
    ADD CONSTRAINT data_export_logs_pkey PRIMARY KEY (id);


--
-- Name: debt_payments debt_payments_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.debt_payments
    ADD CONSTRAINT debt_payments_pkey PRIMARY KEY (id);


--
-- Name: debts debts_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.debts
    ADD CONSTRAINT debts_pkey PRIMARY KEY (id);


--
-- Name: diet_meals diet_meals_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.diet_meals
    ADD CONSTRAINT diet_meals_pkey PRIMARY KEY (id);


--
-- Name: diet_plans diet_plans_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.diet_plans
    ADD CONSTRAINT diet_plans_pkey PRIMARY KEY (id);


--
-- Name: document_summaries document_summaries_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.document_summaries
    ADD CONSTRAINT document_summaries_pkey PRIMARY KEY (id);


--
-- Name: document_versions document_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.document_versions
    ADD CONSTRAINT document_versions_pkey PRIMARY KEY (id);


--
-- Name: documents documents_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_pkey PRIMARY KEY (id);


--
-- Name: emergency_contacts emergency_contacts_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.emergency_contacts
    ADD CONSTRAINT emergency_contacts_pkey PRIMARY KEY (id);


--
-- Name: emergency_log emergency_log_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.emergency_log
    ADD CONSTRAINT emergency_log_pkey PRIMARY KEY (id);


--
-- Name: encrypted_notes encrypted_notes_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.encrypted_notes
    ADD CONSTRAINT encrypted_notes_pkey PRIMARY KEY (id);


--
-- Name: event_budget_items event_budget_items_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.event_budget_items
    ADD CONSTRAINT event_budget_items_pkey PRIMARY KEY (id);


--
-- Name: event_checklists event_checklists_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.event_checklists
    ADD CONSTRAINT event_checklists_pkey PRIMARY KEY (id);


--
-- Name: event_guests event_guests_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.event_guests
    ADD CONSTRAINT event_guests_pkey PRIMARY KEY (id);


--
-- Name: events events_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.events
    ADD CONSTRAINT events_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: family_members family_members_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.family_members
    ADD CONSTRAINT family_members_pkey PRIMARY KEY (id);


--
-- Name: finance finance_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.finance
    ADD CONSTRAINT finance_pkey PRIMARY KEY (id);


--
-- Name: financial_accounts financial_accounts_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.financial_accounts
    ADD CONSTRAINT financial_accounts_pkey PRIMARY KEY (id);


--
-- Name: financial_forecasts financial_forecasts_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.financial_forecasts
    ADD CONSTRAINT financial_forecasts_pkey PRIMARY KEY (id);


--
-- Name: financial_projections financial_projections_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.financial_projections
    ADD CONSTRAINT financial_projections_pkey PRIMARY KEY (id);


--
-- Name: flashcards flashcards_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.flashcards
    ADD CONSTRAINT flashcards_pkey PRIMARY KEY (id);


--
-- Name: freelance_clients freelance_clients_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.freelance_clients
    ADD CONSTRAINT freelance_clients_pkey PRIMARY KEY (id);


--
-- Name: freelance_invoices freelance_invoices_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.freelance_invoices
    ADD CONSTRAINT freelance_invoices_pkey PRIMARY KEY (id);


--
-- Name: freelance_projects freelance_projects_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.freelance_projects
    ADD CONSTRAINT freelance_projects_pkey PRIMARY KEY (id);


--
-- Name: gifts gifts_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.gifts
    ADD CONSTRAINT gifts_pkey PRIMARY KEY (id);


--
-- Name: goal_activities goal_activities_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.goal_activities
    ADD CONSTRAINT goal_activities_pkey PRIMARY KEY (id);


--
-- Name: goals goals_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.goals
    ADD CONSTRAINT goals_pkey PRIMARY KEY (id);


--
-- Name: grocery_items grocery_items_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.grocery_items
    ADD CONSTRAINT grocery_items_pkey PRIMARY KEY (id);


--
-- Name: grocery_lists grocery_lists_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.grocery_lists
    ADD CONSTRAINT grocery_lists_pkey PRIMARY KEY (id);


--
-- Name: gym_exercises gym_exercises_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.gym_exercises
    ADD CONSTRAINT gym_exercises_pkey PRIMARY KEY (id);


--
-- Name: gym_routines gym_routines_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.gym_routines
    ADD CONSTRAINT gym_routines_pkey PRIMARY KEY (id);


--
-- Name: gym_sessions gym_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.gym_sessions
    ADD CONSTRAINT gym_sessions_pkey PRIMARY KEY (id);


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
-- Name: health_profiles health_profiles_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.health_profiles
    ADD CONSTRAINT health_profiles_pkey PRIMARY KEY (id);


--
-- Name: hobbies hobbies_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.hobbies
    ADD CONSTRAINT hobbies_pkey PRIMARY KEY (id);


--
-- Name: home_assets home_assets_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.home_assets
    ADD CONSTRAINT home_assets_pkey PRIMARY KEY (id);


--
-- Name: household_expense_shares household_expense_shares_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.household_expense_shares
    ADD CONSTRAINT household_expense_shares_pkey PRIMARY KEY (id);


--
-- Name: household_expenses household_expenses_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.household_expenses
    ADD CONSTRAINT household_expenses_pkey PRIMARY KEY (id);


--
-- Name: household_tasks household_tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.household_tasks
    ADD CONSTRAINT household_tasks_pkey PRIMARY KEY (id);


--
-- Name: interviews interviews_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.interviews
    ADD CONSTRAINT interviews_pkey PRIMARY KEY (id);


--
-- Name: investment_portfolio investment_portfolio_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.investment_portfolio
    ADD CONSTRAINT investment_portfolio_pkey PRIMARY KEY (id);


--
-- Name: investments investments_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.investments
    ADD CONSTRAINT investments_pkey PRIMARY KEY (id);


--
-- Name: job_applications job_applications_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.job_applications
    ADD CONSTRAINT job_applications_pkey PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: job_logs job_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.job_logs
    ADD CONSTRAINT job_logs_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: journal journal_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.journal
    ADD CONSTRAINT journal_pkey PRIMARY KEY (id);


--
-- Name: knowledge_items knowledge_items_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.knowledge_items
    ADD CONSTRAINT knowledge_items_pkey PRIMARY KEY (id);


--
-- Name: learning_courses learning_courses_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.learning_courses
    ADD CONSTRAINT learning_courses_pkey PRIMARY KEY (id);


--
-- Name: learning_notes learning_notes_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.learning_notes
    ADD CONSTRAINT learning_notes_pkey PRIMARY KEY (id);


--
-- Name: learning learning_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.learning
    ADD CONSTRAINT learning_pkey PRIMARY KEY (id);


--
-- Name: life_advisor_actions life_advisor_actions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.life_advisor_actions
    ADD CONSTRAINT life_advisor_actions_pkey PRIMARY KEY (id);


--
-- Name: life_advisor_briefings life_advisor_briefings_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.life_advisor_briefings
    ADD CONSTRAINT life_advisor_briefings_pkey PRIMARY KEY (id);


--
-- Name: life_area_metrics life_area_metrics_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.life_area_metrics
    ADD CONSTRAINT life_area_metrics_pkey PRIMARY KEY (id);


--
-- Name: life_balance_logs life_balance_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.life_balance_logs
    ADD CONSTRAINT life_balance_logs_pkey PRIMARY KEY (id);


--
-- Name: life_event_predictions life_event_predictions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.life_event_predictions
    ADD CONSTRAINT life_event_predictions_pkey PRIMARY KEY (id);


--
-- Name: maintenance_logs maintenance_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.maintenance_logs
    ADD CONSTRAINT maintenance_logs_pkey PRIMARY KEY (id);


--
-- Name: meal_plans meal_plans_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_pkey PRIMARY KEY (id);


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
-- Name: medication_logs medication_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.medication_logs
    ADD CONSTRAINT medication_logs_pkey PRIMARY KEY (id);


--
-- Name: medications medications_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.medications
    ADD CONSTRAINT medications_pkey PRIMARY KEY (id);


--
-- Name: meditation_sessions meditation_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meditation_sessions
    ADD CONSTRAINT meditation_sessions_pkey PRIMARY KEY (id);


--
-- Name: mood_entries mood_entries_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.mood_entries
    ADD CONSTRAINT mood_entries_pkey PRIMARY KEY (id);


--
-- Name: note_categories note_categories_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.note_categories
    ADD CONSTRAINT note_categories_pkey PRIMARY KEY (id);


--
-- Name: notes notes_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.notes
    ADD CONSTRAINT notes_pkey PRIMARY KEY (id);


--
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- Name: oauth_sessions oauth_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.oauth_sessions
    ADD CONSTRAINT oauth_sessions_pkey PRIMARY KEY (id);


--
-- Name: packing_lists packing_lists_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.packing_lists
    ADD CONSTRAINT packing_lists_pkey PRIMARY KEY (id);


--
-- Name: password_resets password_resets_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.password_resets
    ADD CONSTRAINT password_resets_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_key; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_key UNIQUE (token);


--
-- Name: pomodoro_sessions pomodoro_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.pomodoro_sessions
    ADD CONSTRAINT pomodoro_sessions_pkey PRIMARY KEY (id);


--
-- Name: project_attachments project_attachments_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.project_attachments
    ADD CONSTRAINT project_attachments_pkey PRIMARY KEY (id);


--
-- Name: project_checklists project_checklists_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.project_checklists
    ADD CONSTRAINT project_checklists_pkey PRIMARY KEY (id);


--
-- Name: project_tasks project_tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.project_tasks
    ADD CONSTRAINT project_tasks_pkey PRIMARY KEY (id);


--
-- Name: projects projects_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.projects
    ADD CONSTRAINT projects_pkey PRIMARY KEY (id);


--
-- Name: recipes recipes_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.recipes
    ADD CONSTRAINT recipes_pkey PRIMARY KEY (id);


--
-- Name: relationship_interactions relationship_interactions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.relationship_interactions
    ADD CONSTRAINT relationship_interactions_pkey PRIMARY KEY (id);


--
-- Name: relationships relationships_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.relationships
    ADD CONSTRAINT relationships_pkey PRIMARY KEY (id);


--
-- Name: resume_versions resume_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.resume_versions
    ADD CONSTRAINT resume_versions_pkey PRIMARY KEY (id);


--
-- Name: salary_progress salary_progress_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.salary_progress
    ADD CONSTRAINT salary_progress_pkey PRIMARY KEY (id);


--
-- Name: secure_notes secure_notes_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.secure_notes
    ADD CONSTRAINT secure_notes_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_session_id_key; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_session_id_key UNIQUE (session_id);


--
-- Name: shared_access shared_access_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.shared_access
    ADD CONSTRAINT shared_access_pkey PRIMARY KEY (id);


--
-- Name: shared_modules shared_modules_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.shared_modules
    ADD CONSTRAINT shared_modules_pkey PRIMARY KEY (id);


--
-- Name: shopping_list_items shopping_list_items_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.shopping_list_items
    ADD CONSTRAINT shopping_list_items_pkey PRIMARY KEY (id);


--
-- Name: shopping_lists shopping_lists_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.shopping_lists
    ADD CONSTRAINT shopping_lists_pkey PRIMARY KEY (id);


--
-- Name: sleep_logs sleep_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.sleep_logs
    ADD CONSTRAINT sleep_logs_pkey PRIMARY KEY (id);


--
-- Name: sleep_tracking sleep_tracking_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.sleep_tracking
    ADD CONSTRAINT sleep_tracking_pkey PRIMARY KEY (id);


--
-- Name: smart_goals smart_goals_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.smart_goals
    ADD CONSTRAINT smart_goals_pkey PRIMARY KEY (id);


--
-- Name: stress_logs stress_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.stress_logs
    ADD CONSTRAINT stress_logs_pkey PRIMARY KEY (id);


--
-- Name: subscriptions_advanced subscriptions_advanced_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.subscriptions_advanced
    ADD CONSTRAINT subscriptions_advanced_pkey PRIMARY KEY (id);


--
-- Name: subscriptions subscriptions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_pkey PRIMARY KEY (id);


--
-- Name: symptom_logs symptom_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.symptom_logs
    ADD CONSTRAINT symptom_logs_pkey PRIMARY KEY (id);


--
-- Name: symptoms symptoms_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.symptoms
    ADD CONSTRAINT symptoms_pkey PRIMARY KEY (id);


--
-- Name: task_dependencies task_dependencies_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.task_dependencies
    ADD CONSTRAINT task_dependencies_pkey PRIMARY KEY (id);


--
-- Name: tasks tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_pkey PRIMARY KEY (id);


--
-- Name: tax_documents tax_documents_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.tax_documents
    ADD CONSTRAINT tax_documents_pkey PRIMARY KEY (id);


--
-- Name: team_board_members team_board_members_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.team_board_members
    ADD CONSTRAINT team_board_members_pkey PRIMARY KEY (id);


--
-- Name: team_boards team_boards_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.team_boards
    ADD CONSTRAINT team_boards_pkey PRIMARY KEY (id);


--
-- Name: team_tasks team_tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.team_tasks
    ADD CONSTRAINT team_tasks_pkey PRIMARY KEY (id);


--
-- Name: time_logs time_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.time_logs
    ADD CONSTRAINT time_logs_pkey PRIMARY KEY (id);


--
-- Name: travel_journal travel_journal_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.travel_journal
    ADD CONSTRAINT travel_journal_pkey PRIMARY KEY (id);


--
-- Name: trip_itinerary trip_itinerary_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.trip_itinerary
    ADD CONSTRAINT trip_itinerary_pkey PRIMARY KEY (id);


--
-- Name: trips trips_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.trips
    ADD CONSTRAINT trips_pkey PRIMARY KEY (id);


--
-- Name: user_devices user_devices_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.user_devices
    ADD CONSTRAINT user_devices_pkey PRIMARY KEY (id);


--
-- Name: user_sessions user_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.user_sessions
    ADD CONSTRAINT user_sessions_pkey PRIMARY KEY (id);


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
-- Name: vault_items vault_items_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.vault_items
    ADD CONSTRAINT vault_items_pkey PRIMARY KEY (id);


--
-- Name: vehicle_maintenance vehicle_maintenance_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.vehicle_maintenance
    ADD CONSTRAINT vehicle_maintenance_pkey PRIMARY KEY (id);


--
-- Name: vehicles vehicles_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT vehicles_pkey PRIMARY KEY (id);


--
-- Name: water_goals water_goals_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.water_goals
    ADD CONSTRAINT water_goals_pkey PRIMARY KEY (id);


--
-- Name: water_intake water_intake_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.water_intake
    ADD CONSTRAINT water_intake_pkey PRIMARY KEY (id);


--
-- Name: idx_accounts_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_accounts_user_id ON public.accounts USING btree (user_id);


--
-- Name: idx_activity_logs_created_at; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_activity_logs_created_at ON public.activity_logs USING btree (created_at);


--
-- Name: idx_activity_logs_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_activity_logs_user_id ON public.activity_logs USING btree (user_id);


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
-- Name: idx_bill_payments_bill_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_bill_payments_bill_id ON public.bill_payments USING btree (bill_id);


--
-- Name: idx_bill_payments_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_bill_payments_user_id ON public.bill_payments USING btree (user_id);


--
-- Name: idx_bills_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_bills_user ON public.bills USING btree (user_id);


--
-- Name: idx_bills_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_bills_user_id ON public.bills USING btree (user_id);


--
-- Name: idx_birthdays_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_birthdays_user ON public.birthdays USING btree (user_id);


--
-- Name: idx_books_status; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_books_status ON public.books USING btree (status);


--
-- Name: idx_books_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_books_user ON public.books USING btree (user_id);


--
-- Name: idx_books_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_books_user_id ON public.books USING btree (user_id);


--
-- Name: idx_breathing_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_breathing_user ON public.breathing_exercises USING btree (user_id);


--
-- Name: idx_budget_envelopes_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_budget_envelopes_user ON public.budget_envelopes USING btree (user_id);


--
-- Name: idx_budget_transactions_envelope; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_budget_transactions_envelope ON public.budget_transactions USING btree (envelope_id);


--
-- Name: idx_budgets_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_budgets_user_id ON public.budgets USING btree (user_id);


--
-- Name: idx_calendar_events_start_time; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_calendar_events_start_time ON public.calendar_events USING btree (start_time);


--
-- Name: idx_calendar_events_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_calendar_events_user_id ON public.calendar_events USING btree (user_id);


--
-- Name: idx_calendar_sync_logs_setting; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_calendar_sync_logs_setting ON public.calendar_sync_logs USING btree (sync_setting_id);


--
-- Name: idx_calendar_sync_settings_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_calendar_sync_settings_user ON public.calendar_sync_settings USING btree (user_id);


--
-- Name: idx_career_projects_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_career_projects_user_id ON public.career_projects USING btree (user_id);


--
-- Name: idx_certifications_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_certifications_user ON public.career_certifications USING btree (user_id);


--
-- Name: idx_cloud_backups_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_cloud_backups_user ON public.cloud_backups USING btree (user_id);


--
-- Name: idx_contact_interactions_contact; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_contact_interactions_contact ON public.contact_interactions USING btree (contact_id);


--
-- Name: idx_contacts_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_contacts_user ON public.contacts USING btree (user_id);


--
-- Name: idx_contacts_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_contacts_user_id ON public.contacts USING btree (user_id);


--
-- Name: idx_courses_status; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_courses_status ON public.courses USING btree (status);


--
-- Name: idx_courses_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_courses_user ON public.courses USING btree (user_id);


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
-- Name: idx_daily_briefings_v2_date; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_daily_briefings_v2_date ON public.ai_daily_briefings_v2 USING btree (briefing_date);


--
-- Name: idx_daily_briefings_v2_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_daily_briefings_v2_user ON public.ai_daily_briefings_v2 USING btree (user_id);


--
-- Name: idx_debt_payments_debt; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_debt_payments_debt ON public.debt_payments USING btree (debt_id);


--
-- Name: idx_debts_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_debts_user ON public.debts USING btree (user_id);


--
-- Name: idx_debts_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_debts_user_id ON public.debts USING btree (user_id);


--
-- Name: idx_diet_plans_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_diet_plans_user_id ON public.diet_plans USING btree (user_id);


--
-- Name: idx_documents_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_documents_user_id ON public.documents USING btree (user_id);


--
-- Name: idx_encrypted_notes_created; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_encrypted_notes_created ON public.encrypted_notes USING btree (created_at);


--
-- Name: idx_encrypted_notes_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_encrypted_notes_user ON public.encrypted_notes USING btree (user_id);


--
-- Name: idx_event_guests_event; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_event_guests_event ON public.event_guests USING btree (event_id);


--
-- Name: idx_events_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_events_user ON public.events USING btree (user_id);


--
-- Name: idx_events_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_events_user_id ON public.events USING btree (user_id);


--
-- Name: idx_family_members_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_family_members_user ON public.family_members USING btree (user_id);


--
-- Name: idx_family_members_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_family_members_user_id ON public.family_members USING btree (user_id);


--
-- Name: idx_finance_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_finance_user ON public.finance USING btree (user_id);


--
-- Name: idx_finance_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_finance_user_id ON public.finance USING btree (user_id);


--
-- Name: idx_financial_forecasts_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_financial_forecasts_user_id ON public.financial_forecasts USING btree (user_id);


--
-- Name: idx_flashcards_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_flashcards_user ON public.flashcards USING btree (user_id);


--
-- Name: idx_gifts_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_gifts_user_id ON public.gifts USING btree (user_id);


--
-- Name: idx_goals_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_goals_user ON public.goals USING btree (user_id);


--
-- Name: idx_goals_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_goals_user_id ON public.goals USING btree (user_id);


--
-- Name: idx_gym_routines_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_gym_routines_user_id ON public.gym_routines USING btree (user_id);


--
-- Name: idx_habits_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_habits_user ON public.habits USING btree (user_id);


--
-- Name: idx_habits_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_habits_user_id ON public.habits USING btree (user_id);


--
-- Name: idx_health_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_health_user ON public.health USING btree (user_id);


--
-- Name: idx_hobbies_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_hobbies_user ON public.hobbies USING btree (user_id);


--
-- Name: idx_home_assets_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_home_assets_user_id ON public.home_assets USING btree (user_id);


--
-- Name: idx_household_tasks_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_household_tasks_user ON public.household_tasks USING btree (user_id);


--
-- Name: idx_interviews_job; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_interviews_job ON public.interviews USING btree (job_application_id);


--
-- Name: idx_interviews_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_interviews_user ON public.interviews USING btree (user_id);


--
-- Name: idx_investment_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_investment_user ON public.investment_portfolio USING btree (user_id);


--
-- Name: idx_investments_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_investments_user ON public.investments USING btree (user_id);


--
-- Name: idx_itinerary_trip; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_itinerary_trip ON public.trip_itinerary USING btree (trip_id);


--
-- Name: idx_job_applications_status; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_job_applications_status ON public.job_applications USING btree (status);


--
-- Name: idx_job_applications_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_job_applications_user ON public.job_applications USING btree (user_id);


--
-- Name: idx_job_applications_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_job_applications_user_id ON public.job_applications USING btree (user_id);


--
-- Name: idx_journal_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_journal_user ON public.journal USING btree (user_id);


--
-- Name: idx_learning_courses_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_learning_courses_user_id ON public.learning_courses USING btree (user_id);


--
-- Name: idx_learning_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_learning_user ON public.learning USING btree (user_id);


--
-- Name: idx_life_metrics_date; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_life_metrics_date ON public.life_area_metrics USING btree (metric_date);


--
-- Name: idx_life_metrics_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_life_metrics_user ON public.life_area_metrics USING btree (user_id);


--
-- Name: idx_media_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_media_user ON public.media USING btree (user_id);


--
-- Name: idx_medications_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_medications_user ON public.medications USING btree (user_id);


--
-- Name: idx_medications_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_medications_user_id ON public.medications USING btree (user_id);


--
-- Name: idx_meditation_date; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_meditation_date ON public.meditation_sessions USING btree (session_date);


--
-- Name: idx_meditation_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_meditation_user ON public.meditation_sessions USING btree (user_id);


--
-- Name: idx_mood_entries_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_mood_entries_user_id ON public.mood_entries USING btree (user_id);


--
-- Name: idx_notes_category; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_notes_category ON public.notes USING btree (category);


--
-- Name: idx_notes_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_notes_user ON public.notes USING btree (user_id);


--
-- Name: idx_notifications_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_notifications_user ON public.notifications USING btree (user_id);


--
-- Name: idx_pomodoro_user_date; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_pomodoro_user_date ON public.pomodoro_sessions USING btree (user_id, created_at);


--
-- Name: idx_project_tasks_project; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_project_tasks_project ON public.project_tasks USING btree (project_id);


--
-- Name: idx_projects_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_projects_user ON public.projects USING btree (user_id);


--
-- Name: idx_recipes_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_recipes_user ON public.recipes USING btree (user_id);


--
-- Name: idx_recipes_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_recipes_user_id ON public.recipes USING btree (user_id);


--
-- Name: idx_relationships_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_relationships_user_id ON public.relationships USING btree (user_id);


--
-- Name: idx_shared_access_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_shared_access_user ON public.shared_access USING btree (user_id);


--
-- Name: idx_shared_modules_owner; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_shared_modules_owner ON public.shared_modules USING btree (owner_user_id);


--
-- Name: idx_sleep_date; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_sleep_date ON public.sleep_tracking USING btree (sleep_date);


--
-- Name: idx_sleep_logs_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_sleep_logs_user_id ON public.sleep_logs USING btree (user_id);


--
-- Name: idx_sleep_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_sleep_user ON public.sleep_tracking USING btree (user_id);


--
-- Name: idx_smart_goals_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_smart_goals_user_id ON public.smart_goals USING btree (user_id);


--
-- Name: idx_subscriptions_advanced_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_subscriptions_advanced_user ON public.subscriptions_advanced USING btree (user_id);


--
-- Name: idx_subscriptions_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_subscriptions_user ON public.subscriptions USING btree (user_id);


--
-- Name: idx_symptoms_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_symptoms_user ON public.symptoms USING btree (user_id);


--
-- Name: idx_symptoms_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_symptoms_user_id ON public.symptoms USING btree (user_id);


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
-- Name: idx_tasks_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_tasks_user_id ON public.tasks USING btree (user_id);


--
-- Name: idx_tax_documents_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_tax_documents_user ON public.tax_documents USING btree (user_id);


--
-- Name: idx_tax_documents_year; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_tax_documents_year ON public.tax_documents USING btree (tax_year);


--
-- Name: idx_trips_dates; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_trips_dates ON public.trips USING btree (start_date, end_date);


--
-- Name: idx_trips_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_trips_user ON public.trips USING btree (user_id);


--
-- Name: idx_trips_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_trips_user_id ON public.trips USING btree (user_id);


--
-- Name: idx_user_devices_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_user_devices_user_id ON public.user_devices USING btree (user_id);


--
-- Name: idx_vault_items_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_vault_items_user_id ON public.vault_items USING btree (user_id);


--
-- Name: idx_vehicle_maintenance_vehicle; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_vehicle_maintenance_vehicle ON public.vehicle_maintenance USING btree (vehicle_id);


--
-- Name: idx_vehicles_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_vehicles_user ON public.vehicles USING btree (user_id);


--
-- Name: idx_vehicles_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_vehicles_user_id ON public.vehicles USING btree (user_id);


--
-- Name: idx_water_intake_user_id; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_water_intake_user_id ON public.water_intake USING btree (user_id);


--
-- Name: idx_weekly_summaries_user; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX idx_weekly_summaries_user ON public.ai_weekly_summaries USING btree (user_id);


--
-- Name: accounts accounts_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.accounts
    ADD CONSTRAINT accounts_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: activity_logs activity_logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.activity_logs
    ADD CONSTRAINT activity_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: ai_briefings ai_briefings_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_briefings
    ADD CONSTRAINT ai_briefings_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: ai_chat_contexts ai_chat_contexts_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_chat_contexts
    ADD CONSTRAINT ai_chat_contexts_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: ai_conversations ai_conversations_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_conversations
    ADD CONSTRAINT ai_conversations_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: ai_daily_briefings_v2 ai_daily_briefings_v2_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_daily_briefings_v2
    ADD CONSTRAINT ai_daily_briefings_v2_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: ai_messages ai_messages_conversation_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_messages
    ADD CONSTRAINT ai_messages_conversation_id_fkey FOREIGN KEY (conversation_id) REFERENCES public.ai_conversations(id) ON DELETE CASCADE;


--
-- Name: ai_messages ai_messages_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_messages
    ADD CONSTRAINT ai_messages_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: ai_module_connections ai_module_connections_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_module_connections
    ADD CONSTRAINT ai_module_connections_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: ai_reports ai_reports_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_reports
    ADD CONSTRAINT ai_reports_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: ai_weekly_summaries ai_weekly_summaries_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.ai_weekly_summaries
    ADD CONSTRAINT ai_weekly_summaries_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


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
-- Name: bill_payments bill_payments_bill_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.bill_payments
    ADD CONSTRAINT bill_payments_bill_id_fkey FOREIGN KEY (bill_id) REFERENCES public.bills(id) ON DELETE CASCADE;


--
-- Name: bill_payments bill_payments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.bill_payments
    ADD CONSTRAINT bill_payments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


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
-- Name: books books_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.books
    ADD CONSTRAINT books_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: breathing_exercises breathing_exercises_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.breathing_exercises
    ADD CONSTRAINT breathing_exercises_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: budget_envelopes budget_envelopes_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.budget_envelopes
    ADD CONSTRAINT budget_envelopes_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: budget_transactions budget_transactions_envelope_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.budget_transactions
    ADD CONSTRAINT budget_transactions_envelope_id_fkey FOREIGN KEY (envelope_id) REFERENCES public.budget_envelopes(id) ON DELETE CASCADE;


--
-- Name: budget_transactions budget_transactions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.budget_transactions
    ADD CONSTRAINT budget_transactions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: budgets budgets_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.budgets
    ADD CONSTRAINT budgets_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: calendar_connections calendar_connections_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.calendar_connections
    ADD CONSTRAINT calendar_connections_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: calendar_events calendar_events_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.calendar_events
    ADD CONSTRAINT calendar_events_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: calendar_sync_logs calendar_sync_logs_sync_setting_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.calendar_sync_logs
    ADD CONSTRAINT calendar_sync_logs_sync_setting_id_fkey FOREIGN KEY (sync_setting_id) REFERENCES public.calendar_sync_settings(id) ON DELETE CASCADE;


--
-- Name: calendar_sync_logs calendar_sync_logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.calendar_sync_logs
    ADD CONSTRAINT calendar_sync_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: calendar_sync_settings calendar_sync_settings_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.calendar_sync_settings
    ADD CONSTRAINT calendar_sync_settings_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: career_certifications career_certifications_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.career_certifications
    ADD CONSTRAINT career_certifications_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: career_projects career_projects_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.career_projects
    ADD CONSTRAINT career_projects_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: career_tasks career_tasks_project_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.career_tasks
    ADD CONSTRAINT career_tasks_project_id_fkey FOREIGN KEY (project_id) REFERENCES public.career_projects(id) ON DELETE CASCADE;


--
-- Name: career_tasks career_tasks_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.career_tasks
    ADD CONSTRAINT career_tasks_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: chat_messages chat_messages_session_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.chat_messages
    ADD CONSTRAINT chat_messages_session_id_fkey FOREIGN KEY (session_id) REFERENCES public.chat_sessions(id) ON DELETE CASCADE;


--
-- Name: chat_messages chat_messages_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.chat_messages
    ADD CONSTRAINT chat_messages_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: chat_sessions chat_sessions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.chat_sessions
    ADD CONSTRAINT chat_sessions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: cloud_backups cloud_backups_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.cloud_backups
    ADD CONSTRAINT cloud_backups_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: contact_interactions contact_interactions_contact_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.contact_interactions
    ADD CONSTRAINT contact_interactions_contact_id_fkey FOREIGN KEY (contact_id) REFERENCES public.contacts(id) ON DELETE CASCADE;


--
-- Name: contact_interactions contact_interactions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.contact_interactions
    ADD CONSTRAINT contact_interactions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: contact_reminders contact_reminders_contact_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.contact_reminders
    ADD CONSTRAINT contact_reminders_contact_id_fkey FOREIGN KEY (contact_id) REFERENCES public.contacts(id) ON DELETE CASCADE;


--
-- Name: contact_reminders contact_reminders_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.contact_reminders
    ADD CONSTRAINT contact_reminders_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: contacts contacts_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.contacts
    ADD CONSTRAINT contacts_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: courses courses_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.courses
    ADD CONSTRAINT courses_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


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
-- Name: data_export_logs data_export_logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.data_export_logs
    ADD CONSTRAINT data_export_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: debt_payments debt_payments_debt_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.debt_payments
    ADD CONSTRAINT debt_payments_debt_id_fkey FOREIGN KEY (debt_id) REFERENCES public.debts(id) ON DELETE CASCADE;


--
-- Name: debt_payments debt_payments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.debt_payments
    ADD CONSTRAINT debt_payments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: debts debts_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.debts
    ADD CONSTRAINT debts_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: diet_meals diet_meals_plan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.diet_meals
    ADD CONSTRAINT diet_meals_plan_id_fkey FOREIGN KEY (plan_id) REFERENCES public.diet_plans(id) ON DELETE SET NULL;


--
-- Name: diet_meals diet_meals_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.diet_meals
    ADD CONSTRAINT diet_meals_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: diet_plans diet_plans_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.diet_plans
    ADD CONSTRAINT diet_plans_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: document_summaries document_summaries_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.document_summaries
    ADD CONSTRAINT document_summaries_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: document_versions document_versions_document_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.document_versions
    ADD CONSTRAINT document_versions_document_id_fkey FOREIGN KEY (document_id) REFERENCES public.documents(id) ON DELETE CASCADE;


--
-- Name: document_versions document_versions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.document_versions
    ADD CONSTRAINT document_versions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: documents documents_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: emergency_contacts emergency_contacts_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.emergency_contacts
    ADD CONSTRAINT emergency_contacts_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: emergency_log emergency_log_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.emergency_log
    ADD CONSTRAINT emergency_log_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: encrypted_notes encrypted_notes_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.encrypted_notes
    ADD CONSTRAINT encrypted_notes_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: event_budget_items event_budget_items_event_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.event_budget_items
    ADD CONSTRAINT event_budget_items_event_id_fkey FOREIGN KEY (event_id) REFERENCES public.events(id) ON DELETE CASCADE;


--
-- Name: event_checklists event_checklists_event_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.event_checklists
    ADD CONSTRAINT event_checklists_event_id_fkey FOREIGN KEY (event_id) REFERENCES public.events(id) ON DELETE CASCADE;


--
-- Name: event_guests event_guests_event_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.event_guests
    ADD CONSTRAINT event_guests_event_id_fkey FOREIGN KEY (event_id) REFERENCES public.events(id) ON DELETE CASCADE;


--
-- Name: events events_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.events
    ADD CONSTRAINT events_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: family_members family_members_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.family_members
    ADD CONSTRAINT family_members_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: finance finance_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.finance
    ADD CONSTRAINT finance_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: financial_accounts financial_accounts_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.financial_accounts
    ADD CONSTRAINT financial_accounts_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: financial_forecasts financial_forecasts_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.financial_forecasts
    ADD CONSTRAINT financial_forecasts_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: financial_projections financial_projections_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.financial_projections
    ADD CONSTRAINT financial_projections_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: flashcards flashcards_course_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.flashcards
    ADD CONSTRAINT flashcards_course_id_fkey FOREIGN KEY (course_id) REFERENCES public.learning_courses(id) ON DELETE CASCADE;


--
-- Name: flashcards flashcards_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.flashcards
    ADD CONSTRAINT flashcards_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: freelance_clients freelance_clients_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.freelance_clients
    ADD CONSTRAINT freelance_clients_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: freelance_invoices freelance_invoices_client_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.freelance_invoices
    ADD CONSTRAINT freelance_invoices_client_id_fkey FOREIGN KEY (client_id) REFERENCES public.freelance_clients(id) ON DELETE SET NULL;


--
-- Name: freelance_invoices freelance_invoices_project_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.freelance_invoices
    ADD CONSTRAINT freelance_invoices_project_id_fkey FOREIGN KEY (project_id) REFERENCES public.freelance_projects(id) ON DELETE SET NULL;


--
-- Name: freelance_invoices freelance_invoices_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.freelance_invoices
    ADD CONSTRAINT freelance_invoices_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: freelance_projects freelance_projects_client_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.freelance_projects
    ADD CONSTRAINT freelance_projects_client_id_fkey FOREIGN KEY (client_id) REFERENCES public.freelance_clients(id) ON DELETE SET NULL;


--
-- Name: freelance_projects freelance_projects_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.freelance_projects
    ADD CONSTRAINT freelance_projects_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: gifts gifts_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.gifts
    ADD CONSTRAINT gifts_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: goal_activities goal_activities_goal_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.goal_activities
    ADD CONSTRAINT goal_activities_goal_id_fkey FOREIGN KEY (goal_id) REFERENCES public.smart_goals(id) ON DELETE CASCADE;


--
-- Name: goal_activities goal_activities_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.goal_activities
    ADD CONSTRAINT goal_activities_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: goals goals_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.goals
    ADD CONSTRAINT goals_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: grocery_items grocery_items_grocery_list_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.grocery_items
    ADD CONSTRAINT grocery_items_grocery_list_id_fkey FOREIGN KEY (grocery_list_id) REFERENCES public.grocery_lists(id) ON DELETE CASCADE;


--
-- Name: grocery_lists grocery_lists_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.grocery_lists
    ADD CONSTRAINT grocery_lists_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: gym_exercises gym_exercises_routine_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.gym_exercises
    ADD CONSTRAINT gym_exercises_routine_id_fkey FOREIGN KEY (routine_id) REFERENCES public.gym_routines(id) ON DELETE CASCADE;


--
-- Name: gym_exercises gym_exercises_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.gym_exercises
    ADD CONSTRAINT gym_exercises_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: gym_routines gym_routines_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.gym_routines
    ADD CONSTRAINT gym_routines_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: gym_sessions gym_sessions_routine_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.gym_sessions
    ADD CONSTRAINT gym_sessions_routine_id_fkey FOREIGN KEY (routine_id) REFERENCES public.gym_routines(id) ON DELETE SET NULL;


--
-- Name: gym_sessions gym_sessions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.gym_sessions
    ADD CONSTRAINT gym_sessions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


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
-- Name: health_profiles health_profiles_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.health_profiles
    ADD CONSTRAINT health_profiles_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


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
-- Name: home_assets home_assets_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.home_assets
    ADD CONSTRAINT home_assets_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: household_expense_shares household_expense_shares_family_member_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.household_expense_shares
    ADD CONSTRAINT household_expense_shares_family_member_id_fkey FOREIGN KEY (family_member_id) REFERENCES public.family_members(id) ON DELETE CASCADE;


--
-- Name: household_expense_shares household_expense_shares_household_expense_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.household_expense_shares
    ADD CONSTRAINT household_expense_shares_household_expense_id_fkey FOREIGN KEY (household_expense_id) REFERENCES public.household_expenses(id) ON DELETE CASCADE;


--
-- Name: household_expenses household_expenses_paid_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.household_expenses
    ADD CONSTRAINT household_expenses_paid_by_fkey FOREIGN KEY (paid_by) REFERENCES public.family_members(id);


--
-- Name: household_expenses household_expenses_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.household_expenses
    ADD CONSTRAINT household_expenses_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: household_tasks household_tasks_assigned_to_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.household_tasks
    ADD CONSTRAINT household_tasks_assigned_to_fkey FOREIGN KEY (assigned_to) REFERENCES public.family_members(id);


--
-- Name: household_tasks household_tasks_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.household_tasks
    ADD CONSTRAINT household_tasks_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: interviews interviews_job_application_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.interviews
    ADD CONSTRAINT interviews_job_application_id_fkey FOREIGN KEY (job_application_id) REFERENCES public.job_applications(id) ON DELETE CASCADE;


--
-- Name: interviews interviews_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.interviews
    ADD CONSTRAINT interviews_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: investment_portfolio investment_portfolio_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.investment_portfolio
    ADD CONSTRAINT investment_portfolio_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: investments investments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.investments
    ADD CONSTRAINT investments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: job_applications job_applications_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.job_applications
    ADD CONSTRAINT job_applications_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: job_logs job_logs_job_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.job_logs
    ADD CONSTRAINT job_logs_job_id_fkey FOREIGN KEY (job_id) REFERENCES public.jobs(id) ON DELETE CASCADE;


--
-- Name: journal journal_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.journal
    ADD CONSTRAINT journal_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: knowledge_items knowledge_items_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.knowledge_items
    ADD CONSTRAINT knowledge_items_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: learning_courses learning_courses_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.learning_courses
    ADD CONSTRAINT learning_courses_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: learning_notes learning_notes_course_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.learning_notes
    ADD CONSTRAINT learning_notes_course_id_fkey FOREIGN KEY (course_id) REFERENCES public.learning_courses(id) ON DELETE CASCADE;


--
-- Name: learning_notes learning_notes_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.learning_notes
    ADD CONSTRAINT learning_notes_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: learning learning_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.learning
    ADD CONSTRAINT learning_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: life_advisor_actions life_advisor_actions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.life_advisor_actions
    ADD CONSTRAINT life_advisor_actions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: life_advisor_briefings life_advisor_briefings_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.life_advisor_briefings
    ADD CONSTRAINT life_advisor_briefings_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: life_area_metrics life_area_metrics_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.life_area_metrics
    ADD CONSTRAINT life_area_metrics_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: life_balance_logs life_balance_logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.life_balance_logs
    ADD CONSTRAINT life_balance_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: life_event_predictions life_event_predictions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.life_event_predictions
    ADD CONSTRAINT life_event_predictions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: maintenance_logs maintenance_logs_asset_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.maintenance_logs
    ADD CONSTRAINT maintenance_logs_asset_id_fkey FOREIGN KEY (asset_id) REFERENCES public.home_assets(id) ON DELETE CASCADE;


--
-- Name: maintenance_logs maintenance_logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.maintenance_logs
    ADD CONSTRAINT maintenance_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: meal_plans meal_plans_friday_breakfast_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_friday_breakfast_fkey FOREIGN KEY (friday_breakfast) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_friday_dinner_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_friday_dinner_fkey FOREIGN KEY (friday_dinner) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_friday_lunch_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_friday_lunch_fkey FOREIGN KEY (friday_lunch) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_monday_breakfast_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_monday_breakfast_fkey FOREIGN KEY (monday_breakfast) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_monday_dinner_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_monday_dinner_fkey FOREIGN KEY (monday_dinner) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_monday_lunch_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_monday_lunch_fkey FOREIGN KEY (monday_lunch) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_saturday_breakfast_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_saturday_breakfast_fkey FOREIGN KEY (saturday_breakfast) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_saturday_dinner_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_saturday_dinner_fkey FOREIGN KEY (saturday_dinner) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_saturday_lunch_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_saturday_lunch_fkey FOREIGN KEY (saturday_lunch) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_sunday_breakfast_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_sunday_breakfast_fkey FOREIGN KEY (sunday_breakfast) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_sunday_dinner_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_sunday_dinner_fkey FOREIGN KEY (sunday_dinner) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_sunday_lunch_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_sunday_lunch_fkey FOREIGN KEY (sunday_lunch) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_thursday_breakfast_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_thursday_breakfast_fkey FOREIGN KEY (thursday_breakfast) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_thursday_dinner_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_thursday_dinner_fkey FOREIGN KEY (thursday_dinner) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_thursday_lunch_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_thursday_lunch_fkey FOREIGN KEY (thursday_lunch) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_tuesday_breakfast_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_tuesday_breakfast_fkey FOREIGN KEY (tuesday_breakfast) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_tuesday_dinner_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_tuesday_dinner_fkey FOREIGN KEY (tuesday_dinner) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_tuesday_lunch_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_tuesday_lunch_fkey FOREIGN KEY (tuesday_lunch) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: meal_plans meal_plans_wednesday_breakfast_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_wednesday_breakfast_fkey FOREIGN KEY (wednesday_breakfast) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_wednesday_dinner_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_wednesday_dinner_fkey FOREIGN KEY (wednesday_dinner) REFERENCES public.recipes(id);


--
-- Name: meal_plans meal_plans_wednesday_lunch_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meal_plans
    ADD CONSTRAINT meal_plans_wednesday_lunch_fkey FOREIGN KEY (wednesday_lunch) REFERENCES public.recipes(id);


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
-- Name: medication_logs medication_logs_medication_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.medication_logs
    ADD CONSTRAINT medication_logs_medication_id_fkey FOREIGN KEY (medication_id) REFERENCES public.medications(id) ON DELETE CASCADE;


--
-- Name: medication_logs medication_logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.medication_logs
    ADD CONSTRAINT medication_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: medications medications_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.medications
    ADD CONSTRAINT medications_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: meditation_sessions meditation_sessions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.meditation_sessions
    ADD CONSTRAINT meditation_sessions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: mood_entries mood_entries_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.mood_entries
    ADD CONSTRAINT mood_entries_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: note_categories note_categories_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.note_categories
    ADD CONSTRAINT note_categories_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: notes notes_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.notes
    ADD CONSTRAINT notes_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: notifications notifications_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: oauth_sessions oauth_sessions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.oauth_sessions
    ADD CONSTRAINT oauth_sessions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: packing_lists packing_lists_trip_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.packing_lists
    ADD CONSTRAINT packing_lists_trip_id_fkey FOREIGN KEY (trip_id) REFERENCES public.trips(id) ON DELETE CASCADE;


--
-- Name: packing_lists packing_lists_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.packing_lists
    ADD CONSTRAINT packing_lists_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: password_resets password_resets_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.password_resets
    ADD CONSTRAINT password_resets_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: personal_access_tokens personal_access_tokens_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: pomodoro_sessions pomodoro_sessions_task_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.pomodoro_sessions
    ADD CONSTRAINT pomodoro_sessions_task_id_fkey FOREIGN KEY (task_id) REFERENCES public.tasks(id) ON DELETE CASCADE;


--
-- Name: pomodoro_sessions pomodoro_sessions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.pomodoro_sessions
    ADD CONSTRAINT pomodoro_sessions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: project_attachments project_attachments_project_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.project_attachments
    ADD CONSTRAINT project_attachments_project_id_fkey FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: project_attachments project_attachments_task_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.project_attachments
    ADD CONSTRAINT project_attachments_task_id_fkey FOREIGN KEY (task_id) REFERENCES public.project_tasks(id) ON DELETE CASCADE;


--
-- Name: project_attachments project_attachments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.project_attachments
    ADD CONSTRAINT project_attachments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: project_checklists project_checklists_task_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.project_checklists
    ADD CONSTRAINT project_checklists_task_id_fkey FOREIGN KEY (task_id) REFERENCES public.project_tasks(id) ON DELETE CASCADE;


--
-- Name: project_tasks project_tasks_assigned_to_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.project_tasks
    ADD CONSTRAINT project_tasks_assigned_to_fkey FOREIGN KEY (assigned_to) REFERENCES public.users(id);


--
-- Name: project_tasks project_tasks_project_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.project_tasks
    ADD CONSTRAINT project_tasks_project_id_fkey FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: project_tasks project_tasks_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.project_tasks
    ADD CONSTRAINT project_tasks_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: projects projects_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.projects
    ADD CONSTRAINT projects_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: recipes recipes_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.recipes
    ADD CONSTRAINT recipes_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: relationship_interactions relationship_interactions_relationship_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.relationship_interactions
    ADD CONSTRAINT relationship_interactions_relationship_id_fkey FOREIGN KEY (relationship_id) REFERENCES public.relationships(id) ON DELETE CASCADE;


--
-- Name: relationship_interactions relationship_interactions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.relationship_interactions
    ADD CONSTRAINT relationship_interactions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: relationships relationships_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.relationships
    ADD CONSTRAINT relationships_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: resume_versions resume_versions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.resume_versions
    ADD CONSTRAINT resume_versions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: salary_progress salary_progress_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.salary_progress
    ADD CONSTRAINT salary_progress_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: secure_notes secure_notes_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.secure_notes
    ADD CONSTRAINT secure_notes_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: sessions sessions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: shared_access shared_access_shared_module_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.shared_access
    ADD CONSTRAINT shared_access_shared_module_id_fkey FOREIGN KEY (shared_module_id) REFERENCES public.shared_modules(id) ON DELETE CASCADE;


--
-- Name: shared_access shared_access_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.shared_access
    ADD CONSTRAINT shared_access_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: shared_modules shared_modules_owner_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.shared_modules
    ADD CONSTRAINT shared_modules_owner_user_id_fkey FOREIGN KEY (owner_user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: shopping_list_items shopping_list_items_shopping_list_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.shopping_list_items
    ADD CONSTRAINT shopping_list_items_shopping_list_id_fkey FOREIGN KEY (shopping_list_id) REFERENCES public.shopping_lists(id) ON DELETE CASCADE;


--
-- Name: shopping_lists shopping_lists_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.shopping_lists
    ADD CONSTRAINT shopping_lists_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: sleep_logs sleep_logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.sleep_logs
    ADD CONSTRAINT sleep_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: sleep_tracking sleep_tracking_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.sleep_tracking
    ADD CONSTRAINT sleep_tracking_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: smart_goals smart_goals_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.smart_goals
    ADD CONSTRAINT smart_goals_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: stress_logs stress_logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.stress_logs
    ADD CONSTRAINT stress_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: subscriptions_advanced subscriptions_advanced_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.subscriptions_advanced
    ADD CONSTRAINT subscriptions_advanced_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: subscriptions subscriptions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: symptom_logs symptom_logs_symptom_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.symptom_logs
    ADD CONSTRAINT symptom_logs_symptom_id_fkey FOREIGN KEY (symptom_id) REFERENCES public.symptoms(id) ON DELETE CASCADE;


--
-- Name: symptom_logs symptom_logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.symptom_logs
    ADD CONSTRAINT symptom_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: symptoms symptoms_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.symptoms
    ADD CONSTRAINT symptoms_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: task_dependencies task_dependencies_depends_on_task_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.task_dependencies
    ADD CONSTRAINT task_dependencies_depends_on_task_id_fkey FOREIGN KEY (depends_on_task_id) REFERENCES public.tasks(id) ON DELETE CASCADE;


--
-- Name: task_dependencies task_dependencies_task_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.task_dependencies
    ADD CONSTRAINT task_dependencies_task_id_fkey FOREIGN KEY (task_id) REFERENCES public.tasks(id) ON DELETE CASCADE;


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
-- Name: tax_documents tax_documents_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.tax_documents
    ADD CONSTRAINT tax_documents_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: team_board_members team_board_members_board_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.team_board_members
    ADD CONSTRAINT team_board_members_board_id_fkey FOREIGN KEY (board_id) REFERENCES public.team_boards(id) ON DELETE CASCADE;


--
-- Name: team_board_members team_board_members_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.team_board_members
    ADD CONSTRAINT team_board_members_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: team_boards team_boards_owner_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.team_boards
    ADD CONSTRAINT team_boards_owner_id_fkey FOREIGN KEY (owner_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: team_tasks team_tasks_assigned_to_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.team_tasks
    ADD CONSTRAINT team_tasks_assigned_to_fkey FOREIGN KEY (assigned_to) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: team_tasks team_tasks_board_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.team_tasks
    ADD CONSTRAINT team_tasks_board_id_fkey FOREIGN KEY (board_id) REFERENCES public.team_boards(id) ON DELETE CASCADE;


--
-- Name: time_logs time_logs_project_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.time_logs
    ADD CONSTRAINT time_logs_project_id_fkey FOREIGN KEY (project_id) REFERENCES public.career_projects(id) ON DELETE CASCADE;


--
-- Name: time_logs time_logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.time_logs
    ADD CONSTRAINT time_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: travel_journal travel_journal_trip_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.travel_journal
    ADD CONSTRAINT travel_journal_trip_id_fkey FOREIGN KEY (trip_id) REFERENCES public.trips(id) ON DELETE CASCADE;


--
-- Name: travel_journal travel_journal_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.travel_journal
    ADD CONSTRAINT travel_journal_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: trip_itinerary trip_itinerary_trip_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.trip_itinerary
    ADD CONSTRAINT trip_itinerary_trip_id_fkey FOREIGN KEY (trip_id) REFERENCES public.trips(id) ON DELETE CASCADE;


--
-- Name: trip_itinerary trip_itinerary_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.trip_itinerary
    ADD CONSTRAINT trip_itinerary_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: trips trips_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.trips
    ADD CONSTRAINT trips_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: user_devices user_devices_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.user_devices
    ADD CONSTRAINT user_devices_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: user_sessions user_sessions_device_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.user_sessions
    ADD CONSTRAINT user_sessions_device_id_fkey FOREIGN KEY (device_id) REFERENCES public.user_devices(id) ON DELETE CASCADE;


--
-- Name: user_sessions user_sessions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.user_sessions
    ADD CONSTRAINT user_sessions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: vault_items vault_items_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.vault_items
    ADD CONSTRAINT vault_items_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: vehicle_maintenance vehicle_maintenance_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.vehicle_maintenance
    ADD CONSTRAINT vehicle_maintenance_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: vehicle_maintenance vehicle_maintenance_vehicle_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.vehicle_maintenance
    ADD CONSTRAINT vehicle_maintenance_vehicle_id_fkey FOREIGN KEY (vehicle_id) REFERENCES public.vehicles(id) ON DELETE CASCADE;


--
-- Name: vehicles vehicles_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT vehicles_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: water_goals water_goals_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.water_goals
    ADD CONSTRAINT water_goals_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: water_intake water_intake_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.water_intake
    ADD CONSTRAINT water_intake_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: neondb_owner
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;


--
-- PostgreSQL database dump complete
--

