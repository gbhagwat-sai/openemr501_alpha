<?php /* Smarty version 2.6.31, created on 2019-07-12 09:16:37
         compiled from C:/xampp/htdocs/openemr_lim501/templates/insurance_companies/general_edit.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'C:/xampp/htdocs/openemr_lim501/templates/insurance_companies/general_edit.html', 13, false),array('function', 'xl', 'C:/xampp/htdocs/openemr_lim501/templates/insurance_companies/general_edit.html', 16, false),array('function', 'html_options', 'C:/xampp/htdocs/openemr_lim501/templates/insurance_companies/general_edit.html', 106, false),)), $this); ?>
<form name="insurancecompany" method="post" action="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
" class='form-horizontal' onsubmit="return top.restoreSession()">
    <!-- it is important that the hidden form_id field be listed first, when it is called it populates any old information attached with the id, this allows for partial edits
    if it were called last, the settings from the form would be overwritten with the old information-->
    <input type="hidden" name="form_id" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->id)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
    <?php if ($this->_tpl_vars['insurancecompany']->get_inactive() == 1): ?>
    <div class="form-group">
        <label for="inactive" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Reactivate')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
        <div class="col-sm-8">
            <input type="checkbox" id="inactive" name="inactive" class="checkbox" value="0" />
        </div>
    </div>
    <?php endif; ?>
    <?php if ($this->_tpl_vars['insurancecompany']->get_inactive() == 0): ?>
    <div class="form-group">
        <label for="inactive" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Deactivate')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
        <div class="col-sm-8">
            <input type="checkbox" id="inactive" name="inactive" class="checkbox" value="1" />
        </div>
    </div>
    <?php endif; ?>
    <div class="form-group">
        <label for="name" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Name')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="name" name="name" class="form-control" aria-describedby="nameHelpBox" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_name())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" onKeyDown="PreventIt(event)">
            <span id="nameHelpBox" class="help-block">(<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Required')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
)</span>
        </div>
    </div>
    <div class="form-group">
        <label for="attn" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Attn')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="attn" name="attn" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_attn())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <!-- Sai custom code start -->
    <div class="form-group">
        <label for="attn" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Insurance Code')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="attn" name="ins_code" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_ins_code())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>

<!-- Sai custom code end -->
    <div class="form-group">
        <label for="address_line1" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Address')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="address_line1" name="address_line1" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->address->line1)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-group">
        <label for="address_line2" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Address')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="address_line2" name="address_line2" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->address->line2)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-group">
        <label for="city" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='City')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="city" name="city" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->address->city)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-group">
        <label for="state" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='State')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
        <div class="col-sm-8">
            <input type="text" maxlength="2" id="state" name="state" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->address->state)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-group">
        <label for="zip" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Zip Code')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="zip" name="zip" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->address->zip)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-group">
        <label for="phone" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Phone')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="phone" name="phone" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_phone())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <div class="form-group">
        <label for="cms_id" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='CMS ID')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
        <div class="col-sm-8">
            <input type="text" id="cms_id" name="cms_id" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_cms_id())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>
    <?php if ($this->_tpl_vars['SUPPORT_ENCOUNTER_CLAIMS']): ?>
        <div class="form-group">
            <label for="alt_cms_id" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => 'CMS ID For Encounter Claims'), $this);?>
</label>
            <div class="col-sm-8">
                <input type="text" id="alt_cms_id" name="alt_cms_id" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_alt_cms_id())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" onKeyDown="PreventIt(event)">
            </div>
        </div>
    <?php endif; ?>
    <div class="form-group">
        <label for="ins_type_code" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Payer Type')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
        <div class="col-sm-8">
            <select id="ins_type_code" name="ins_type_code" class="form-control">
                <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['insurancecompany']->ins_type_code_array,'selected' => $this->_tpl_vars['insurancecompany']->get_ins_type_code()), $this);?>

            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="x12_default_partner_id" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Default X12 Partner')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
        <div class="col-sm-8">
            <select id="x12_default_partner_id" name="x12_default_partner_id" class="form-control">
                <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['x12_partners'],'selected' => $this->_tpl_vars['insurancecompany']->get_x12_default_partner_id()), $this);?>

            </select>
        </div>
    </div>
    <!-- Sai custom code start -->
<!-- code added for patient statement BUG 11205------->
<div class="form-group">
        <label for="statement_limit" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Statement Limit')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
        <div class="col-sm-8">
             <input type="text" id="statement_limit" name="alt_cms_id" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->get_statement_limit())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" onKeyDown="PreventIt(event)">
        </div>
    </div>

<!-- Sai custom code end -->
    <div class="btn-group col-sm-offset-2">
        <a href="javascript:submit_insurancecompany();" class="btn btn-default btn-save" onclick="top.restoreSession()">
            <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Save')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>

        </a>
        <a href="controller.php?practice_settings&insurance_company&action=list" class="btn btn-link btn-cancel" onclick="top.restoreSession()">
            <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Cancel')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>

        </a>
    </div>
    <input type="hidden" name="id" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['insurancecompany']->id)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
    <input type="hidden" name="process" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['PROCESS'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
</form>

<?php echo '
<script language="javascript">
    function submit_insurancecompany() {
        if(document.insurancecompany.name.value.length>0) {
            top.restoreSession();
            document.insurancecompany.submit();
            //Z&H Removed redirection
        } else{
            document.insurancecompany.name.style.backgroundColor="red";
            document.insurancecompany.name.focus();
        }
    }

    function jsWaitForDelay(delay) {
        var startTime = new Date();
        var endTime = null;
        do {
            endTime = new Date();
        } while ((endTime - startTime) < delay);
    }
// Sai custom code start
$(function() {
  $(\'#disc_amount1\').on(\'change\', function(e) {
    e.stopPropagation();
   this.value = this.checked ? 1 : 0;
   
	if(this.value==1)
		$(\'#disc_amount\').val(\'1\');
	else 
		$(\'#disc_amount\').val(\'0\');
  });
  
 

})
// Sai custom code end
</script>
'; ?>