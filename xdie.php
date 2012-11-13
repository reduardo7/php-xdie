<?php

/**
 * Copy this function somewhere that is accessible from the entire project.
 * Example: Into your "bootstrap".
 */

function XDIE() {
    $trace = debug_backtrace();
    // Call
    $call = $trace[0]['file'] . ":" . $trace[0]['line'];
    // Read parameters
    if (isset($trace[0]['file'])) {
        $expr = "";
        // Get file
        $lines = file($trace[0]['file']);
        $l = $trace[0]['line'];
        // Find "XDIE" function call
        while (strpos($expr, __FUNCTION__) === false) $expr = trim($lines[--$l]) . "\n" . $expr;
        $expr = substr($expr, strpos($expr, __FUNCTION__) + strlen(__FUNCTION__));
        // Remove first "("
        $expr = substr($expr, strpos($expr, "(") + 1);
        // Remove ");"
        $l = 1;
        $i = 0;
        while (($l > 0) && ($i < strlen($expr))) {
            $c = $expr[$i++];
            if ($c==")") {
                $l--;
            } elseif ($c=="(") {
                $l++;
            }
        }
        $expr = substr($expr, 0, --$i);
        // Remove comments
        if (!defined('T_ML_COMMENT')) {
            define('T_ML_COMMENT', T_COMMENT);
        } else {
            define('T_DOC_COMMENT', T_ML_COMMENT);
        }
        $tokens = token_get_all("<?php $expr ?>");
        $result = "";
        foreach ($tokens as $token) {
            if (is_string($token)) {
                $result .= $token;
            } else {
                list($id, $text) = $token;
                switch($token) {
                    case T_COMMENT: case T_ML_COMMENT: case T_DOC_COMMENT: break;
                    default: $result .= $text; break;
                }
            }
        }
        // Remove "\n"
        $result = str_replace("\n", " ", $result);
        // Remove PHP tags
        $result = trim(substr($result, 5, strlen($result) - 7));
        // Parts
        $params = array();
        $l = false;
        $last = 0;
        $ec = 0;
        for ($i = 0; $i < strlen($result); $i++) {
            $c = $result[$i];
            if (!$l && in_array($c, array("(", '"', "'", "["))) {
                $l = true;
            } elseif ($l && in_array($c, array(")", '"', "'", "]")) && ($ec % 2 == 0)) {
                $l = false;
            } elseif(!$l && ($c == ",")) {
                $params[] = trim(substr($result, $last, $i - $last));
                $last = $i + 1;
            }
            // Escape char
            if ($c == "\\") {
                $ec++;
            } else {
                $ec = 0;
            }
        }
        $params[] = trim(substr($result, $last, $i - $last));
    } else {
        $params = array();
        foreach(func_get_args() as $i => $var) $params[] = $i;
    }
//     echo "#---"; var_dump($params); echo "---#"; exit(1);

    $s = "<!-- " . str_repeat("-", 120) . " -->"; $t = "\n$s"; $n = "\n$t";

    if (isset($_SERVER["HTTP_HOST"])) {
        // HTTP | Browser
        $PS = "margin:5;padding:5px;background:#DDDDDD;";
        echo "\n<!-- :: XDIE :: -->$n<h1 style=\"color:#FF0000;\" onclick=\"javascript:_xdieSH('XDIE-BODY');window.location='#XDIE';\">XDIE</h1><div id=\"XDIE-CONT\" style=\"z-index:99999;border:2px solid #AAAAAA;font-family:monospace;position:absolute;display:block;top:0px;left:0px;background:#FFAAAA;width:100%;\">"
            . "<h2 onclick=\"javascript:_xdieSH('XDIE-BODY')\" align=\"center\" id=\"XDIE\">:: XDIE ::</h2><script type=\"text/javascript\">"
            . "function _xdieSH(i){var e=document.getElementById(i);if(e.style.display==''){e.style.display='none';}else{e.style.display='';}}"
            . "function _xdieShow(i){_xdieSH('var'+i);}"
            . "function _xdieView(i,v){document.getElementById('varPrint'+i).style.display=v?'':'none';document.getElementById('varDump'+i).style.display=v?'none':'';}"
            . "</script><div id=\"XDIE-BODY\">\n$call";
        foreach (func_get_args() as $i => $var) {
            $v = "PARAM[$i] = $params[$i]";
            echo "<hr/><h3><a title=\"Show/Hide\" href=\"javascript:_xdieShow($i)\">$v</a></h3>";
            echo "<div id=\"var$i\" style=\"font-size: 11px;\"><a href=\"javascript:_xdieView($i,true)\">print_r</a> <a href=\"javascript:_xdieView($i,false)\">var_dump</a><pre id=\"varPrint$i\" style=\"$PS\">\n\n<!-- ######### $v ######### -->\n";
            print_r($var);
            echo "\n$s</pre><pre id=\"varDump$i\" style=\"$PS;display:none\">\n";
            var_dump($var);
            echo "$n</pre></div>";
        }
        echo "</div><script type=\"text/javascript\">var t=document.getElementById('XDIE-CONT').getElementsByTagName('pre');for(i in t){e=t[i];if(typeof(e)!='object')break;e.innerHTML=e.innerHTML.replace(/<!--.*?-->/g,'').trim();}</script></div>\n\n<!-- END XDIE -->";
    } else {
        // Console
        echo "\n:: XDIE ::\n$call\n\n";
        foreach (func_get_args() as $i => $var) {
            $v = "PARAM[$i] = $params[$i]";
            echo "\n######### $v\n";
            print_r($var);
            echo "\n$s\n";
            var_dump($var);
            echo $n;
        }
        echo "\n\nEND XDIE";
    }
    echo "\n\n";
    exit();
}









// EXAMPLE SHOULD BE PASS

$x =   trim( " hola ")  ;  XDIE  (

    array(1 => 2, "a\"'\\" =>
trim($x)), $student_id, // THIS IS A COMMENT!

/*THIS IS AN OTHER COMMENT!

$x
*/

$x
)  ; exit ("FIN!!!");
