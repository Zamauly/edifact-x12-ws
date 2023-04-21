<?php
echo "Test CSV View";
?>
<div>
    <p>Carga de Csv</p>
    <form action="" method="post" enctype="multipart/form-data">
        Select csv to upload:
        <input type="file" name="csvToUpload" id="csvToUpload" accept=".csv,.tsv" required onchange="validateCsvExt()">
        <input type="submit" value="Upload CSV" name="submit-csv-file">
    </form>

</div>
<script>
    function validateCsvExt(){
        let processedFile = document.getElementById("csvToUpload").value,
        extension = processedFile.substring(processedFile.lastIndexOf("."),processedFile.length);

        //console.log(new Date()+" [ validaeExt ] extension: "+extension);
        if(document.getElementById('csvToUpload').getAttribute('accept').split(',').indexOf(extension) < 0) {
            alert('Archivo inválido. No se permite la extensión ' + extension);
            clearFileInput("csvToUpload");
            
        }
  
    }
    
    function clearFileInput(id) { 
        var oldInput = document.getElementById(id); 

        var newInput = document.createElement("input"); 

        newInput.type = "file"; 
        newInput.id = oldInput.id; 
        newInput.name = oldInput.name; 
        newInput.className = oldInput.className; 
        newInput.style.cssText = oldInput.style.cssText; 
        // TODO: copy any other relevant attributes 

        oldInput.parentNode.replaceChild(newInput, oldInput); 
    }
</script>