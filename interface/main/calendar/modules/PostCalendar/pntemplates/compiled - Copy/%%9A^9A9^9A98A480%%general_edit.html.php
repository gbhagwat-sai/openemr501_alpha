<?php /* Smarty version 2.6.31, created on 2019-12-03 10:07:12
         compiled from C:/docroot/templates/insurance_numbers/general_edit.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'C:/docroot/templates/insurance_numbers/general_edit.html', 11, false),array('function', 'xl', 'C:/docroot/templates/insurance_numbers/general_edit.html', 25, false),array('function', 'html_options', 'C:/docroot/templates/insurance_numbers/general_edit.html', 76, false),)), $this); ?>
<?php if ($this->_tpl_vars['ERROR']): ?>
    <div class="alert alert-danger"><?php echo ((is_array($_tmp=$this->_tpl_vars['ERROR'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</div>
<?php else: ?>
    <form name="provider" method="post" action="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
" class='form-horizontal' onsubmit="return top.restoreSession()">
        <!-- it is important that the hidden form_id field be listed first, when it is called it populates any old information attached with the id, this allows for partial edits
                    if it were called last, the settings from the form would be overwritten with the old information-->
        <input type="hidden" name="form_id" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['ins']->id)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />

        <table class="table table-responsive table-striped">

        <tr><td colspan="5" style="border-style:none;" class="bold">
            <?php echo ((is_array($_tmp=$this->_tpl_vars['provider']->get_name_display())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

        </td></tr>

        <tr  class="showborder_head">
            <th class="small"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Company Name')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</th>
	<!-- Sai custom code start -->
            <th class="small"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Provider Number')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</th>
            <th class="small"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Rendering Provider Number')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</th>
            <th class="small"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Group Number')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</th>
        <th class="small"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Federal TAX ID #')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</th>
            <th class="small"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Practice NPI')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</th>
				<!-- Sai custom code end -->
        </tr>
        <?php $_from = $this->_tpl_vars['provider']->get_insurance_numbers(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['inums'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['inums']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['num_set']):
        $this->_foreach['inums']['iteration']++;
?>
            <tr>
                <td valign="middle">
                    <a href="<?php echo $this->_tpl_vars['CURRENT_ACTION']; ?>
action=edit&id=<?php echo ((is_array($_tmp=$this->_tpl_vars['num_set']->get_id())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
&showform=true" onclick="top.restoreSession()">
                        <?php echo ((is_array($_tmp=$this->_tpl_vars['num_set']->get_insurance_company_name())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
&nbsp;
                    </a>
                </td>
                <td><?php echo ((is_array($_tmp=$this->_tpl_vars['num_set']->get_provider_number())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
&nbsp;</td>
                <td><?php echo ((is_array($_tmp=$this->_tpl_vars['num_set']->get_rendering_provider_number())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
&nbsp;</td>
                <td><?php echo ((is_array($_tmp=$this->_tpl_vars['num_set']->get_group_number())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
&nbsp;</td>
				<!-- Sai custom code start -->
        		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['num_set']->get_federal_tax_id_number())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
&nbsp;</td>
            	<td><?php echo ((is_array($_tmp=$this->_tpl_vars['num_set']->get_practice_npi())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
&nbsp;</td>
				<!-- Sai custom code end -->
            </tr>
        <?php endforeach; else: ?>
        <tr>
            <td colspan="5"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='No entries found, use the form below to add an entry.')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</td>
        </tr>
        <?php endif; unset($_from); ?>

        <tr> <td style="border-style:none;" colspan="5">
            <a href="<?php echo $this->_tpl_vars['CURRENT_ACTION']; ?>
action=edit&id=&provider_id=<?php echo ((is_array($_tmp=$this->_tpl_vars['provider']->get_id())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
&showform=true"
                class="btn btn-default btn-add" onclick="top.restoreSession()">
                <span><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Add New')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>

            </a>
        </td> </tr>
        </table>

        <?php if ($this->_tpl_vars['show_edit_gui']): ?>
            <div class="alert alert-info">
                <?php if ($this->_tpl_vars['ins']->get_id() == ""): ?>
                    <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Add Provider Number')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>

                <?php else: ?>
                    <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Update Provider Number')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>

                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="insurance_company_id" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Insurance Company')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
                <div class="col-sm-8">
                    <?php if ($this->_tpl_vars['ins']->get_id() == ""): ?>
                        <select id="insurance_company_id" name="insurance_company_id" class="form-control">
                            <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['ic_array'],'values' => $this->_tpl_vars['ic_array'],'selected' => $this->_tpl_vars['ins']->get_insurance_company_id()), $this);?>

                        </select>
                    <?php else: ?>
                        <span id="insurance_company_id" class="form-control-static">
                            <?php echo ((is_array($_tmp=$this->_tpl_vars['ins']->get_insurance_company_name())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="form-group">
                <label for="provider_number" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Provider Number')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
                <div class="col-sm-8">
                    <input type="text" id="provider_number" name="provider_number" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['ins']->get_provider_number())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" onKeyDown="PreventIt(event)">
                </div>
            </div>
            <div class="form-group">
                <label for="provider_number_type" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => 'Provider Number (Type)'), $this);?>
</label>
                <div class="col-sm-8">
                    <select id="provider_number_type" name="provider_number_type" class="form-control">
                        <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['ic_type_options_array'],'values' => $this->_tpl_vars['ins']->provider_number_type_array,'selected' => $this->_tpl_vars['ins']->get_provider_number_type()), $this);?>

                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="rendering_provider_number" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Rendering Provider Number')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
                <div class="col-sm-8">
                    <input type="text" id="rendering_provider_number" name="rendering_provider_number" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['ins']->get_rendering_provider_number())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" onKeyDown="PreventIt(event)">
                </div>
            </div>
            <div class="form-group">
                <label for="rendering_provider_number_type" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Rendering Provider Number (Type)')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
                <div class="col-sm-8">
                    <select id="rendering_provider_number_type" name="rendering_provider_number_type" class="form-control">
                        <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['ic_rendering_type_options_array'],'values' => $this->_tpl_vars['ins']->rendering_provider_number_type_array,'selected' => $this->_tpl_vars['ins']->get_rendering_provider_number_type()), $this);?>

                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="group_number" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Group Number')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
                <div class="col-sm-8">
                    <input type="text" id="group_number" name="group_number" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['ins']->get_group_number())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" onKeyDown="PreventIt(event)">
                </div>
            </div>

<!-- Sai custom code start -->
		<div class="form-group">
                <label for="federal_tax_id_number_type" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Federal TAX ID Number (Box 25)')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
                <div class="col-sm-8">
                    <?php echo smarty_function_html_options(array('name' => 'federal_tax_id_number_type','options' => $this->_tpl_vars['ins']->federal_tax_id_number_type_option,'selected' => $this->_tpl_vars['ins']->get_federal_tax_id_number_type()), $this);?>

        
                <input type="text" size="20" name="federal_tax_id_number" value="<?php echo $this->_tpl_vars['ins']->get_federal_tax_id_number(); ?>
" onKeyDown="PreventIt(event)" />
                </div>
            </div>
			
			<div class="form-group">
                <label for="practice_npi" class="control-label col-sm-2"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Practice NPI (Box 33a)')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
                <div class="col-sm-8">
                    <input type="text" size="20" name="practice_npi" value="<?php echo $this->_tpl_vars['ins']->get_practice_npi(); ?>
" onKeyDown="PreventIt(event)" />
                </div>
            </div>
	<!-- Sai custom code end -->

            <div class="btn-group col-sm-offset-2">
                <?php if ($this->_tpl_vars['ins']->get_id() == ""): ?>
                    <a href="javascript:submit_insurancenumbers_add();" class="btn btn-default btn-save" onclick="top.restoreSession()">
                        <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Save')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>

                    </a>
                <?php else: ?>
                    <a href="javascript:submit_insurancenumbers_update();" class="btn btn-default btn-save" onclick="top.restoreSession()">
                        <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Save')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>

                    </a>
                <?php endif; ?>
                <a href="controller.php?practice_settings&insurance_numbers&action=list" class="btn btn-link btn-cancel" onclick="top.restoreSession()">
                    <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Cancel')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>

                </a>
            </div>

        <?php else: ?>
            <input type="hidden" name="provider_number" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['ins']->get_provider_number())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
            <input type="hidden" name="provider_number_type" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['ins']->get_provider_number_type())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
            <input type="hidden" name="rendering_provider_number" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['ins']->get_rendering_provider_number())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
            <input type="hidden" name="rendering_provider_number_type" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['ins']->get_rendering_provider_number_type())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
            <input type="hidden" name="group_number" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['ins']->get_group_number())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
			<!-- Sai custom code start -->
			<input type="hidden" name="federal_tax_id_number" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['ins']->get_federal_tax_id_number())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
			<input type="hidden" name="practice_npi" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['ins']->get_practice_npi())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
			<input type="hidden" name="federal_tax_id_number_type" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['ins']->get_federal_tax_id_number_type())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
				<!-- Sai custom code end -->
        <?php endif; ?>

        <input type="hidden" name="id" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['ins']->id)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
        <input type="hidden" name="provider_id" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['ins']->get_provider_id())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
        <input type="hidden" name="process" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['PROCESS'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
    </form>
<?php endif; ?>

<?php echo '
<script language="javascript">
function submit_insurancenumbers_update() {
    top.restoreSession();
    document.provider.submit();
}
function submit_insurancenumbers_add() {
    top.restoreSession();
    document.provider.submit();
    //Z&H Removed redirection
}

function Waittoredirect(delaymsec) {
 var st = new Date();
 var et = null;
 do {
 et = new Date();
 } while ((et - st) < delaymsec);

 }
</script>
<style type="text/css">
text,select {font-size:9pt;}
</style>
'; ?>