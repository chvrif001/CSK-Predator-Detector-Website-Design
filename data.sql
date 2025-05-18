--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.10
-- Dumped by pg_dump version 9.6.10

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: _cskpredatordetector_db; Type: TABLE; Schema: public; Owner: rebasedata
--

CREATE TABLE public._cskpredatordetector_db (
    id smallint,
    userid bigint,
    first_name character varying(6) DEFAULT NULL::character varying,
    last_name character varying(9) DEFAULT NULL::character varying,
    email character varying(23) DEFAULT NULL::character varying,
    password smallint,
    url_address character varying(16) DEFAULT NULL::character varying,
    date character varying(19) DEFAULT NULL::character varying
);


ALTER TABLE public._cskpredatordetector_db OWNER TO rebasedata;

--
-- Data for Name: _cskpredatordetector_db; Type: TABLE DATA; Schema: public; Owner: rebasedata
--

COPY public._cskpredatordetector_db (id, userid, first_name, last_name, email, password, url_address, date) FROM stdin;
1	80200405168	rifuwo	chavalala	chavalalafuwo@gmail.com	1234	rifuwo.chavalala	2025-05-05 14:28:43
\.


--
-- PostgreSQL database dump complete
--

