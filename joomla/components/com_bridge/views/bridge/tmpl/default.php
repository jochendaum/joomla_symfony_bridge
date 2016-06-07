<?php $document = &JFactory::getDocument();
if ($document->getType() != 'raw' && !JRequest::getInt('plugin')){
?><div class="row">

<?php if(intval(JRequest::getVar('cols')) == 12){?>

	<div class="twelvecol last">

    <?php echo $this->symfony;?>

<?php }else{?>

    <div class="eightcol">
    <?php echo $this->symfony;?>
    </div>


<div class="fourcol last"></div>


</div>

<?php
    }

}else{
    echo $this->symfony;

}?>