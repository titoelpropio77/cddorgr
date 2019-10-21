<?php
$linespersession    = 3000;   		// Lines to be executed per one import session
$delaypersession    = 0;      		// You can specify a sleep time in milliseconds after each session
function create_xml_response() {
    global $linenumber, $foffset, $totalqueries, $curfilename, $delimiter,
    $lines_this, $lines_done, $lines_togo, $lines_tota,
    $queries_this, $queries_done, $queries_togo, $queries_tota,
    $bytes_this, $bytes_done, $bytes_togo, $bytes_tota,
    $kbytes_this, $kbytes_done, $kbytes_togo, $kbytes_tota,
    $mbytes_this, $mbytes_done, $mbytes_togo, $mbytes_tota,
    $pct_this, $pct_done, $pct_togo, $pct_tota, $pct_bar;
    
    $linenumber = $_REQUEST[start] + 1;
    $foffset = $_REQUEST[foffset];
    $totalqueries = $_REQUEST[totalqueries];
    $curfilename = $_REQUEST[fn];
    $delimiter = ';';
    $pct_bar = 50;
    
    if ($linenumber < 5) {

    header('Content-Type: application/xml');
    header('Cache-Control: no-cache');

    echo '<?xml version="1.0" encoding="ISO-8859-1"?>';
    echo "<root>";

// data - for calculations

    echo "<linenumber>$linenumber</linenumber>";
    echo "<foffset>$foffset</foffset>";
    echo "<fn>$curfilename</fn>";
    echo "<totalqueries>$totalqueries</totalqueries>";
    echo "<delimiter>$delimiter</delimiter>";

// results - for page update

    echo "<elem1>$lines_this</elem1>";
    echo "<elem2>$lines_done</elem2>";
    echo "<elem3>$lines_togo</elem3>";
    echo "<elem4>$lines_tota</elem4>";

    echo "<elem5>$queries_this</elem5>";
    echo "<elem6>$queries_done</elem6>";
    echo "<elem7>$queries_togo</elem7>";
    echo "<elem8>$queries_tota</elem8>";

    echo "<elem9>$bytes_this</elem9>";
    echo "<elem10>$bytes_done</elem10>";
    echo "<elem11>$bytes_togo</elem11>";
    echo "<elem12>$bytes_tota</elem12>";

    echo "<elem13>$kbytes_this</elem13>";
    echo "<elem14>$kbytes_done</elem14>";
    echo "<elem15>$kbytes_togo</elem15>";
    echo "<elem16>$kbytes_tota</elem16>";

    echo "<elem17>$mbytes_this</elem17>";
    echo "<elem18>$mbytes_done</elem18>";
    echo "<elem19>$mbytes_togo</elem19>";
    echo "<elem20>$mbytes_tota</elem20>";

    echo "<elem21>$pct_this</elem21>";
    echo "<elem22>$pct_done</elem22>";
    echo "<elem23>$pct_togo</elem23>";
    echo "<elem24>$pct_tota</elem24>";
    echo "<elem_bar>" . htmlentities($pct_bar) . "</elem_bar>";

    echo "</root>";
    } else {
        echo "<p style='color:green;'>HECHO POR LEY</p>";
    }
}

function create_ajax_script() {
    global $linenumber, $foffset, $totalqueries, $delaypersession, $curfilename, $delimiter;
    $linenumber = $_REQUEST[start];
    $foffset = $_REQUEST[foffset];
    $totalqueries = $_REQUEST[totalqueries];
//    $delaypersession = $_REQUEST[start];
    $curfilename = $_REQUEST[fn];
    $delimiter = ';';
    ?>

    <script type="text/javascript" language="javascript">

    // creates next action url (upload page, or XML response)
        function get_url(linenumber, fn, foffset, totalqueries, delimiter) {
            return "<?php echo $_SERVER['PHP_SELF'] ?>?start=" + linenumber + "&fn=" + fn + "&foffset=" + foffset + "&totalqueries=" + totalqueries + "&delimiter=" + delimiter + "&ajaxrequest=true";
        }

    // extracts text from XML element (itemname must be unique)
        function get_xml_data(itemname, xmld) {
            return xmld.getElementsByTagName(itemname).item(0).firstChild.data;
        }

        function makeRequest(url) {
            http_request = false;
            if (window.XMLHttpRequest) {
                // Mozilla etc.
                http_request = new XMLHttpRequest();
                if (http_request.overrideMimeType) {
                    http_request.overrideMimeType("text/xml");
                }
            } else if (window.ActiveXObject) {
                // IE
                try {
                    http_request = new ActiveXObject("Msxml2.XMLHTTP");
                } catch (e) {
                    try {
                        http_request = new ActiveXObject("Microsoft.XMLHTTP");
                    } catch (e) {
                    }
                }
            }
            if (!http_request) {
                alert("No se puede crear una instancia XMLHTTP");
                return false;
            }
            http_request.onreadystatechange = server_response;
            http_request.open("GET", url, true);
            http_request.send(null);
        }

        function server_response()
        {

            // waiting for correct response
            if (http_request.readyState != 4)
                return;

            if (http_request.status != 200)
            {
                alert("&iexcl;La URL de la página no est&aacute; disponible!")
                return;
            }

            // r = xml response
            var r = http_request.responseXML;

            //if received not XML but HTML with new page to show
            if (!r || r.getElementsByTagName('root').length == 0)
            {
                var text = http_request.responseText;
                document.open();
                document.write(text);
                document.close();
                return;
            }

            // update "Starting from line: "
//            document.getElementsByTagName('p').item(1).innerHTML =
//                    "Comenzar en la l&iacute;nea: " +
//                    r.getElementsByTagName('linenumber').item(0).firstChild.nodeValue;

            // update table with new values
            for (i = 1; i <= 24; i++)
//                document.getElementsByTagName('td').item(i).firstChild.data = get_xml_data('elem' + i, r);

            // update color bar
//            document.getElementsByTagName('td').item(25).innerHTML =
//                    r.getElementsByTagName('elem_bar').item(0).firstChild.nodeValue;

            // action url (XML response)	 
            url_request = get_url(
                    get_xml_data('linenumber', r),
                    get_xml_data('fn', r),
                    get_xml_data('foffset', r),
                    get_xml_data('totalqueries', r),
                    get_xml_data('delimiter', r));

            // ask for XML response	
            window.setTimeout("makeRequest(url_request)", 500 +<?php echo $delaypersession; ?>);
        }

    // First Ajax request from initial page

        var http_request = false;
        var url_request = get_url(<?php echo ($linenumber . ',"' . urlencode($curfilename) . '",' . $foffset . ',' . $totalqueries . ',"' . urlencode($delimiter) . '"'); ?>);
        window.setTimeout("makeRequest(url_request)", 500 +<?php echo $delaypersession; ?>);
    </script>

    <?php
}

$ajax = true;
if ($ajax)
    ob_start();

if ($ajax && isset($_REQUEST['start'])) {
    if (isset($_REQUEST['ajaxrequest'])) {
        ob_end_clean();
        create_xml_response();
        die;
    } else
        create_ajax_script();
}
ob_flush();