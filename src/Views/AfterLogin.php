<html>

<head>
    <style>
        #tabs ul li 
        {
            display:inline-block;
            /* float:left; */
            height:24px;
            min-width:80px;
            text-align:center;
            line-height: 22px;
            padding:0 8px 0 8px;
            margin: 1px 0px 0px 0px;
            border: 1px solid gray;
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;	
            background:#F0F0F0;
        }
    </style>
    
<!--     <link href="./TabPanel.css" type="text/css" rel="stylesheet" >
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script> -->
</head>

<body>
    <?php
    $user = $_SESSION["user"] ?? null;
    if(!empty($user)&&$user!==null)
        echo "Welcome ".$user["data"]->getUserName();
    ?>
   <div id="tabs">
        <ul>
            <li onClick="selView(1, this)">Carga catalogo</li>
            <li onClick="selView(2, this)">Carga archivo</li>
        </ul>
    </div>

    <div id="tabcontent">
        <div id="csvupload" class="tabpanel" style="display:inline">
            <?php require_once APP_ROOT."/Views/UploadCsv.php"; ?>
        </div>
        <div id="ediupload" class="tabpanel" style="display:none">
            <?php require_once APP_ROOT."/Views/UploadEdi.php"; ?>
        </div>
    </div>
</body>
<script>
    function selView(n, litag) {
        var svgview = "none";
        var codeview = "none";
        switch(n) {
            case 1:
            svgview = "inline";
            break;
            case 2:
            codeview = "inline";
            break;
            // add how many cases you need
            default:
            break;
        }

        document.getElementById("csvupload").style.display = svgview;
        document.getElementById("ediupload").style.display = codeview;
        var tabs = document.getElementById("tabs");
        var ca = Array.prototype.slice.call(tabs.querySelectorAll("li"));
        ca.map(function(elem) {
            elem.style.background="#F0F0F0";
            elem.style.borderBottom="1px solid gray"
        });

        litag.style.borderBottom = "1px solid white";
        litag.style.background = "white";
    }

    function selInit() {
        var tabs = document.getElementById("tabs");
        var litag = tabs.querySelector("li");   // first li
        litag.style.borderBottom = "1px solid white";
        litag.style.background = "white";
    }
</script>
</html>