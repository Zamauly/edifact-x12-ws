<?php
echo "Test Edi View";
?>
<div>
    <p>Carga de Edi</p>
    <form action="" method="post" enctype="multipart/form-data">
        Select edi to upload:
        <input type="file" name="ediToUpload" id="ediToUpload" accept=".edi,.edifact,.x12" required onchange="validateEdiExt()">
        <input type="submit" value="Upload EDI" name="submit-edi-file">
    </form>
    
</div>
<script>
    function validateEdiExt(){
        let processedFile = document.getElementById("ediToUpload").value,
        extension = processedFile.substring(processedFile.lastIndexOf("."),processedFile.length);

        //console.log(new Date()+" [ validaeExt ] extension: "+extension);
        if(document.getElementById('ediToUpload').getAttribute('accept').split(',').indexOf(extension) < 0) {
            alert('Archivo inválido. No se permite la extensión ' + extension);
            clearFileInput("ediToUpload");
            
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