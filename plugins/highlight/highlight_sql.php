<?php
/* This software is licensed through a BSD-style License.
 * http://www.opensource.org/licenses/bsd-license.php

Copyright (c) 2003, 2004, Jacob D. Cohen
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:

Redistributions of source code must retain the above copyright notice,
this list of conditions and the following disclaimer.
Redistributions in binary form must reproduce the above copyright
notice, this list of conditions and the following disclaimer in the
documentation and/or other materials provided with the distribution.
Neither the name of Jacob D. Cohen nor the names of his contributors
may be used to endorse or promote products derived from this software
without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/

/*

Special thanks to E.J. Brocklesby for providing PL/I language support.
Special thanks to F.R. for the XML language support.

*/

function keyword_replace($keywords, $text, $ncs = false)
{
    $cm = ($ncs)? "i" : "";
    foreach ($keywords as $keyword)
    {
        $search[]  = "/(\\b$keyword\\b)/" . $cm;
        $replace[] = '<span class="keyword">\\0</span>';
    }

    $search[]  = "/(\\bclass\s)/";
    $replace[] = '<span class="keyword">\\0</span>';

    return preg_replace($search, $replace, $text);
}


function preproc_replace($preproc, $text)
{
    foreach ($preproc as $proc)
    {
        $search[] = "/(\\s*#\s*$proc\\b)/";
        $replace[] = '<span class="keyword">\\0</span>';
    }

    return preg_replace($search, $replace, $text);
}


function sch_syntax_helper($text)
{
    return $text;
}


function syntax_highlight_helper($text, $language)
{
    $preproc = array();
   

    $keywords = array(
    
    "SQL" => array(
    "abort", "abs", "absolute", "access",
    "action", "ada", "add", "admin",
    "after", "aggregate", "alias", "all",
    "allocate", "alter", "analyse", "analyze",
    "and", "any", "are", "array",
    "as", "asc", "asensitive", "assertion",
    "assignment", "asymmetric", "at", "atomic",
    "authorization", "avg", "backward", "before",
    "begin", "between", "bigint", "binary",
    "bit", "bitvar", "bit_length", "blob",
    "boolean", "both", "breadth", "by",
    "c", "cache", "call", "called",
    "cardinality", "cascade", "cascaded", "case",
    "cast", "catalog", "catalog_name", "chain",
    "char", "character", "characteristics", "character_length",
    "character_set_catalog", "character_set_name", "character_set_schema", "char_length",
    "check", "checked", "checkpoint", /* "class", */
    "class_origin", "clob", "close", "cluster",
    "coalesce", "cobol", "collate", "collation",
    "collation_catalog", "collation_name", "collation_schema", "column",
    "column_name", "command_function", "command_function_code", "comment",
    "commit", "committed", "completion", "condition_number",
    "connect", "connection", "connection_name", "constraint",
    "constraints", "constraint_catalog", "constraint_name", "constraint_schema",
    "constructor", "contains", "continue", "conversion",
    "convert", "copy", "corresponding", "count",
    "create", "createdb", "createuser", "cross",
    "cube", "current", "current_date", "current_path",
    "current_role", "current_time", "current_timestamp", "current_user",
    "cursor", "cursor_name", "cycle", "data",
    "database", "date", "datetime_interval_code", "datetime_interval_precision",
    "day", "deallocate", "dec", "decimal",
    "declare", "default", "defaults", "deferrable",
    "deferred", "defined", "definer", "delete",
    "delimiter", "delimiters", "depth", "deref",
    "desc", "describe", "descriptor", "destroy",
    "destructor", "deterministic", "diagnostics", "dictionary",
    "disconnect", "dispatch", "distinct", "do",
    "domain", "double", "drop", "dynamic",
    "dynamic_function", "dynamic_function_code", "each", "else",
    "encoding", "encrypted", "end", "end-exec",
    "equals", "escape", "every", "except",
    "exception", "excluding", "exclusive", "exec",
    "execute", "existing", "exists", "explain",
    "external", "extract", "false", "fetch",
    "final", "first", "float", "for",
    "force", "foreign", "fortran", "forward",
    "found", "free", "freeze", "from",
    "full", "function", "g", "general",
    "generated", "get", "global", "go",
    "goto", "grant", "granted", "group",
    "grouping", "handler", "having", "hierarchy",
    "hold", "host", "hour", "identity",
    "ignore", "ilike", "immediate", "immutable",
    "implementation", "implicit", "in", "including",
    "increment", "index", "indicator", "infix",
    "inherits", "initialize", "initially", "inner",
    "inout", "input", "insensitive", "insert",
    "instance", "instantiable", "instead", "int",
    "integer", "intersect", "interval", "into",
    "invoker", "is", "isnull", "isolation",
    "iterate", "join", "k", "key",
    "key_member", "key_type", "lancompiler", "language",
    "large", "last", "lateral", "leading",
    "left", "length", "less", "level",
    "like", "limit", "listen", "load",
    "local", "localtime", "localtimestamp", "location",
    "locator", "lock", "lower", "m",
    "map", "match", "max", "maxvalue",
    "message_length", "message_octet_length", "message_text", "method",
    "min", "minute", "minvalue", "mod",
    "mode", "modifies", "modify", "module",
    "month", "more", "move", "mumps",
    "name", "names", "national", "natural",
    "nchar", "nclob", "new", "next",
    "no", "nocreatedb", "nocreateuser", "none",
    "not", "nothing", "notify", "notnull",
    "null", "nullable", "nullif", "number",
    "numeric", "object", "octet_length", "of",
    "off", "offset", "oids", "old",
    "on", "only", "open", "operation",
    "operator", "option", "options", "or",
    "order", "ordinality", "out", "outer",
    "output", "overlaps", "overlay", "overriding",
    "owner", "pad", "parameter", "parameters",
    "parameter_mode", "parameter_name", "parameter_ordinal_position", "parameter_specific_catalog",
    "parameter_specific_name", "parameter_specific_schema", "partial", "pascal",
    "password", "path", "pendant", "placing",
    "pli", "position", "postfix", "precision",
    "prefix", "preorder", "prepare", "preserve",
    "primary", "prior", "privileges", "procedural",
    "procedure", "public", "read", "reads",
    "real", "recheck", "recursive", "ref",
    "references", "referencing", "reindex", "relative",
    "rename", "repeatable", "replace", "reset",
    "restart", "restrict", "result", "return",
    "returned_length", "returned_octet_length", "returned_sqlstate", "returns",
    "revoke", "right", "role", "rollback",
    "rollup", "routine", "routine_catalog", "routine_name",
    "routine_schema", "row", "rows", "row_count",
    "rule", "savepoint", "scale", "schema",
    "schema_name", "scope", "scroll", "search",
    "second", "section", "security", "select",
    "self", "sensitive", "sequence", "serializable",
    "server_name", "session", "session_user", "set",
    "setof", "sets", "share", "show",
    "similar", "simple", "size", "smallint",
    "some", "source", "space", "specific",
    "specifictype", "specific_name", "sql", "sqlcode",
    "sqlerror", "sqlexception", "sqlstate", "sqlwarning",
    "stable", "start", "state", "statement",
    "static", "statistics", "stdin", "stdout",
    "storage", "strict", "structure", "style",
    "subclass_origin", "sublist", "substring", "sum",
    "symmetric", "sysid", "system", "system_user",
    "table", "table_name", "temp", "template",
    "temporary", "terminate", "text", "than", "then",
    "time", "timestamp", "timezone_hour", "timezone_minute",
    "to", "toast", "trailing", "transaction",
    "transactions_committed", "transactions_rolled_back", "transaction_active", "transform",
    "transforms", "translate", "translation", "treat",
    "trigger", "trigger_catalog", "trigger_name", "trigger_schema",
    "trim", "true", "truncate", "trusted",
    "type", "uncommitted", "under", "unencrypted",
    "union", "unique", "unknown", "unlisten",
    "unnamed", "unnest", "until", "update",
    "upper", "usage", "user", "user_defined_type_catalog",
    "user_defined_type_name", "user_defined_type_schema", "using", "vacuum",
    "valid", "validator", "value", "values",
    "varchar", "variable", "varying", "verbose",
    "version", "view", "volatile", "when",
    "whenever", "where", "with", "without",
    "work", "write", "year", "zone")

    );

    $case_insensitive = array(
        "SQL"    => true
    );
    $ncs = false;
    if (array_key_exists($language, $case_insensitive))
        $ncs = true;

    $text = (array_key_exists($language, $preproc))?
        preproc_replace($preproc[$language], $text) :
        $text;
    $text = (array_key_exists($language, $keywords))?
        keyword_replace($keywords[$language], $text, $ncs) :
        $text;

    return $text;
}


function rtrim1($span, $lang, $ch)
{
    return syntax_highlight_helper(substr($span, 0, -1), $lang);
}


function rtrim1_htmlesc($span, $lang, $ch)
{
    return htmlspecialchars(substr($span, 0, -1));
}


function sch_rtrim1($span, $lang, $ch)
{
    return sch_syntax_helper(substr($span, 0, -1));
}


function rtrim2($span, $lang, $ch)
{
    return substr($span, 0, -2);
}


function syn_proc($span, $lang, $ch)
{
    return syntax_highlight_helper($span, $lang);
}

function dash_putback($span, $lang, $ch)
{
    return syntax_highlight_helper('-' . $span, $lang);
}

function slash_putback($span, $lang, $ch)
{
    return syntax_highlight_helper('/' . $span, $lang);
}

function slash_putback_rtrim1($span, $lang, $ch)
{
    return rtrim1('/' . $span, $lang, $ch);
}

function lparen_putback($span, $lang, $ch)
{
    return syntax_highlight_helper('(' . $span, $lang);
}

function lparen_putback_rtrim1($span, $lang, $ch)
{
    return rtrim1('(' . $span, $lang, $ch);
}

function prepend_xml_opentag($span, $lang, $ch) 
{                                               
    return '<span class="xml_tag">&lt;' . $span;
}                                               

function proc_void($span, $lang, $ch)
{
    return $span;
}


/**
 * Syntax highlight function
 * Does the bulk of the syntax highlighting by lexing the input
 * string, then calling the helper function to highlight keywords.
 */
function syntax_highlight($text, $language)
{
    if ($language == "Plain Text") return $text;

    define("normal_text",   1, true);
    define("dq_literal",    2, true);
    define("dq_escape",     3, true);
    define("sq_literal",    4, true);
    define("sq_escape",     5, true);
    define("slash_begin",   6, true);
    define("star_comment",  7, true);
    define("star_end",      8, true);
    define("line_comment",  9, true);
    define("html_entity",  10, true);
    define("lc_escape",    11, true);
    define("block_comment",12, true);
    define("paren_begin",  13, true);
    define("dash_begin",   14, true);
    define("bt_literal",   15, true);
    define("bt_escape",    16, true);
    define("xml_tag_begin",17, true);
    define("xml_tag",      18, true);
    define("xml_pi",       19, true);
    define("sch_normal",   20, true);
    define("sch_stresc",    21, true);
    define("sch_idexpr",   22, true);
    define("sch_numlit",   23, true);
    define("sch_chrlit",   24, true);
    define("sch_strlit",   25, true);

    $initial_state["Scheme"] = sch_normal;

    $sch[sch_normal][0]     = sch_normal;
    $sch[sch_normal]['"']   = sch_strlit;
    $sch[sch_normal]["#"]   = sch_chrlit;
    $sch[sch_normal]["0"]   = sch_numlit;
    $sch[sch_normal]["1"]   = sch_numlit;
    $sch[sch_normal]["2"]   = sch_numlit;
    $sch[sch_normal]["3"]   = sch_numlit;
    $sch[sch_normal]["4"]   = sch_numlit;
    $sch[sch_normal]["5"]   = sch_numlit;
    $sch[sch_normal]["6"]   = sch_numlit;
    $sch[sch_normal]["7"]   = sch_numlit;
    $sch[sch_normal]["8"]   = sch_numlit;
    $sch[sch_normal]["9"]   = sch_numlit;

    $sch[sch_strlit]['"']   = sch_normal;
    $sch[sch_strlit]["\n"]  = sch_normal;
    $sch[sch_strlit]["\\"]  = sch_stresc;
    $sch[sch_strlit][0]     = sch_strlit;

    $sch[sch_chrlit][" "]   = sch_normal;
    $sch[sch_chrlit]["\t"]  = sch_normal;
    $sch[sch_chrlit]["\n"]  = sch_normal;
    $sch[sch_chrlit]["\r"]  = sch_normal;
    $sch[sch_chrlit][0]     = sch_chrlit;

    $sch[sch_numlit][" "]   = sch_normal;
    $sch[sch_numlit]["\t"]  = sch_normal;
    $sch[sch_numlit]["\n"]  = sch_normal;
    $sch[sch_numlit]["\r"]  = sch_normal;
    $sch[sch_numlit][0]     = sch_numlit;

   

    $sql[normal_text]['"']     = dq_literal;
    $sql[normal_text]["'"]     = sq_literal;
    $sql[normal_text]['`']     = bt_literal;
    $sql[normal_text]['-']     = dash_begin;
    $sql[normal_text][0]       = normal_text;

    $sql[dq_literal]['"']      = normal_text;
    $sql[dq_literal]['\\']     = dq_escape;
    $sql[dq_literal][0]        = dq_literal;

    $sql[sq_literal]["'"]      = normal_text;
    $sql[sq_literal]['\\']     = sq_escape;
    $sql[sq_literal][0]        = sq_literal;

    $sql[bt_literal]['`']      = normal_text;
    $sql[bt_literal]['\\']     = bt_escape;
    $sql[bt_literal][0]        = bt_literal;

    $sql[dq_escape][0]         = dq_literal;
    $sql[sq_escape][0]         = sq_literal;
    $sql[bt_escape][0]         = bt_literal;

    $sql[dash_begin]["-"]      = line_comment;
    $sql[dash_begin][0]        = normal_text;

    $sql[line_comment]["\n"]   = normal_text;
    $sql[line_comment]["\\"]   = lc_escape;
    $sql[line_comment][0]      = line_comment;

    $sql[lc_escape]["\r"]      = lc_escape;
    $sql[lc_escape][0]         = line_comment;

   
    //
    // Main state transition table
    //
    $states = array(
        "SQL"  => $sql
    );


    //
    // Process functions
    //
    $process["C89"][normal_text][sq_literal] = "rtrim1";
    $process["C89"][normal_text][dq_literal] = "rtrim1";
    $process["C89"][normal_text][slash_begin] = "rtrim1";
    $process["C89"][normal_text][0] = "syn_proc";

    $process["C89"][slash_begin][star_comment] = "rtrim1";
    $process["C89"][slash_begin][0] = "slash_putback";

    $process["Scheme"][sch_normal][sch_strlit] = "sch_rtrim1";
    $process["Scheme"][sch_normal][sch_chrlit] = "sch_rtrim1";
    $process["Scheme"][sch_normal][sch_numlit] = "sch_rtrim1";

    $process["SQL"][normal_text][sq_literal] = "rtrim1";
    $process["SQL"][normal_text][dq_literal] = "rtrim1";
    $process["SQL"][normal_text][bt_literal] = "rtrim1";
    $process["SQL"][normal_text][dash_begin] = "rtrim1";
    $process["SQL"][normal_text][0] = "syn_proc";

    $process["SQL"][dash_begin][line_comment] = "rtrim1";
    $process["SQL"][dash_begin][0] = "dash_putback";

    

    $process_end["C89"] = "syntax_highlight_helper";
    $process_end["C++"] = $process_end["C89"];
    $process_end["C"] = $process_end["C89"];
    $process_end["PHP"] = $process_end["C89"];
    $process_end["Perl"] = $process_end["C89"];
    $process_end["Java"] = $process_end["C89"];
    $process_end["VB"] = $process_end["C89"];
    $process_end["C#"] = $process_end["C89"];
    $process_end["Ruby"] = $process_end["C89"];
    $process_end["Python"] = $process_end["C89"];
    $process_end["Pascal"] = $process_end["C89"];
    $process_end["mIRC"] = $process_end["C89"];
    $process_end["PL/I"] = $process_end["C89"];
    $process_end["SQL"] = $process_end["C89"];
    $process_end["Scheme"] = "sch_syntax_helper";


    $edges["C89"][normal_text .",". dq_literal]   = '<span class="literal">"';
    $edges["C89"][normal_text .",". sq_literal]   = '<span class="literal">\'';
    $edges["C89"][slash_begin .",". star_comment] = '<span class="comment">/*';
    $edges["C89"][dq_literal .",". normal_text]   = '</span>';
    $edges["C89"][sq_literal .",". normal_text]   = '</span>';
    $edges["C89"][star_end .",". normal_text]     = '</span>';

    $edges["Scheme"][sch_normal .",". sch_strlit] = '<span class="sch_str">"';
    $edges["Scheme"][sch_normal .",". sch_numlit] = '<span class="sch_num">';
    $edges["Scheme"][sch_normal .",". sch_chrlit] = '<span class="sch_chr">#';
    $edges["Scheme"][sch_strlit .",". sch_normal] = '</span>';
    $edges["Scheme"][sch_numlit .",". sch_normal] = '</span>';
    $edges["Scheme"][sch_chrlit .",". sch_normal] = '</span>';

    $edges["SQL"][normal_text .",". dq_literal]   = '<span class="literal">"';
    $edges["SQL"][normal_text .",". sq_literal]   = '<span class="literal">\'';
    $edges["SQL"][dash_begin .",". line_comment] = '<span class="comment">--';
    $edges["SQL"][normal_text .",". bt_literal]   = '`';
    $edges["SQL"][dq_literal .",". normal_text]   = '</span>';
    $edges["SQL"][sq_literal .",". normal_text]   = '</span>';
    $edges["SQL"][line_comment .",". normal_text] = '</span>';

   

    //
    // The State Machine
    //
    if (array_key_exists($language, $initial_state))
        $state = $initial_state[$language];
    else
        $state = normal_text;
    $output = "";
    $span = "";
    while (strlen($text) > 0)
    {
        $ch = substr($text, 0, 1);
        $text = substr($text, 1);

        $oldstate = $state;
        $state = (array_key_exists($ch, $states[$language][$state]))?
            $states[$language][$state][$ch] :
            $states[$language][$state][0];

        $span .= $ch;

        if ($oldstate != $state)
        {
            if (array_key_exists($language, $process) &&
                array_key_exists($oldstate, $process[$language]))
            {
                if (array_key_exists($state, $process[$language][$oldstate]))
                {
                    $pf = $process[$language][$oldstate][$state];
                    $output .= $pf($span, $language, $ch);
                }
                else
                {
                    $pf = $process[$language][$oldstate][0];
                    $output .= $pf($span, $language, $ch);
                }
            }
            else
            {
                $output .= $span;
            }

            if (array_key_exists($language, $edges) &&
                array_key_exists("$oldstate,$state", $edges[$language]))
                $output .= $edges[$language]["$oldstate,$state"];

            $span = "";
        }
    }

    if (array_key_exists($language, $process_end) && $state == normal_text)
        $output .= $process_end[$language]($span, $language);
    else
        $output .= $span;

    if ($state != normal_text)
    {
        if (array_key_exists($language, $edges) &&
            array_key_exists("$state," . normal_text, $edges[$language]))
            $output .= $edges[$language]["$state," . normal_text];
    }
                
    return $output;
}

?>