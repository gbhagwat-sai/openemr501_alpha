<?php /* Smarty version 2.6.31, created on 2019-06-05 10:53:15
         compiled from C:%5Cxampp%5Chtdocs%5Copenemr_lim501%5Cinterface%5Cforms%5Cros/templates/ros/general_new.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xl', 'C:\\xampp\\htdocs\\openemr_lim501\\interface\\forms\\ros/templates/ros/general_new.html', 13, false),array('function', 'headerTemplate', 'C:\\xampp\\htdocs\\openemr_lim501\\interface\\forms\\ros/templates/ros/general_new.html', 15, false),array('function', 'html_radios', 'C:\\xampp\\htdocs\\openemr_lim501\\interface\\forms\\ros/templates/ros/general_new.html', 58, false),array('modifier', 'escape', 'C:\\xampp\\htdocs\\openemr_lim501\\interface\\forms\\ros/templates/ros/general_new.html', 13, false),)), $this); ?>
<html>
<head>

<title><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Review Of Systems')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</title>

<?php echo smarty_function_headerTemplate(array(), $this);?>

<?php echo '
 <style type="text/css" title="mystyles" media="all">
    label {
        padding: 0px 5px !Important;
    }
    input[type=checkbox], input[type=radio] {
    margin: 4px;
    }
    @media only screen and (max-width: 1220px) {
        [class*="col-"] {
        width: 100%;
        text-align:left!Important;
    }
</style>
<!-- Sai custom code start -->
<script type="text/javascript" src="../../library/textformat.js"></script>
<script language="javascript">
document.onkeydown = function(evt) 
{
    masterkeypress(evt);
}
</script>
<!-- Sai custom code end  -->
'; ?>

</head>
<body class="body_top">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="page-header">
                    <h2><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Review Of Systems')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</h2>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <form name="ros" class="form-horizontal" method="post" action="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
/interface/forms/ros/save.php" onsubmit="return top.restoreSession()">
                    <fieldset>
                        <legend class=""><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Constitutional')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</legend>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="weight_change" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Weight Change')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'weight_change','id' => 'weight_change','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_weight_change(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="anorexia" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Anorexia')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'anorexia','id' => 'anorexia','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_anorexia(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="night_sweats" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Night Sweats')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
                                <?php echo smarty_function_html_radios(array('name' => 'night_sweats','id' => 'night_sweats','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_night_sweats(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="heat_or_cold" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Heat or Cold')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'heat_or_cold','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_heat_or_cold(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="weakness" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Weakness')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
                                <?php echo smarty_function_html_radios(array('name' => 'weakness','id' => 'weakness','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_weakness(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="fever" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Fever')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'fever','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_fever(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="insomnia" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Insomnia')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'insomnia','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_insomnia(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="intolerance" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Intolerance')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'intolerance','id' => 'intolerance','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_intolerance(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="fatigue" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Fatigue')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'fatigue','id' => 'fatigue','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_fatigue(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="weight_change" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Chills')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'chills','id' => 'chills','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_chills(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="irritability" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Irritability')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'irritability','id' => 'irritability','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_irritability(),'separator' => ""), $this);?>

                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class=""><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Eyes')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</legend>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="change_in_vision" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Change in Vision')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'change_in_vision','id' => 'change_in_vision','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_weight_change(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="irritation" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Irritation')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'irritation','id' => 'irritation','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_irritation(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="double_vision" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Double Vision')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'double_vision','id' => 'double_vision','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_double_vision(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="glaucoma_history" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Family History of Glaucoma')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'glaucoma_history','id' => 'glaucoma_history','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_glaucoma_history(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="redness" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Redness')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'redness','id' => 'redness','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_redness(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="blind_spots" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Blind Spots')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'blind_spots','id' => 'blind_spots','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_blind_spots(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="eye_pain" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Eye Pain')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'eye_pain','id' => 'eye_pain','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_eye_pain(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="excessive_tearing" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Excessive Tearing')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'excessive_tearing','id' => 'excessive_tearing','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_excessive_tearing(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="photophobia" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Photophobia')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'photophobia','id' => 'photophobia','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_photophobia(),'separator' => ""), $this);?>

                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class=""><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Ears')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
, <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Nose')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
, <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Mouth')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
, <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Throat')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</legend>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="hearing_loss" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Hearing Loss')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'hearing_loss','id' => 'hearing_loss','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_hearing_loss(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="vertigo" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Vertigo')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'vertigo','id' => 'vertigo','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_vertigo(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="sore_throat" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Sore Throat')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'sore_throat','id' => 'sore_throat','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_sore_throat(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="nosebleed" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Nosebleed')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'nosebleed','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_nosebleed(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="discharge" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Discharge')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'discharge','id' => 'discharge','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_discharge(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="tinnitus" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Tinnitus')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'tinnitus','id' => 'tinnitus','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_tinnitus(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="sinus_problems" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Sinus Problems')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'sinus_problems','id' => 'sinus_problems','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_sinus_problems(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="snoring" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Snoring')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'snoring','id' => 'snoring','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_snoring(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="pain" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Pain')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'pain','id' => 'pain','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_pain(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="frequent_colds" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Frequent Colds')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'frequent_colds','id' => 'frequent_colds','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_frequent_colds(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="post_nasal_drip" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Post Nasal Drip')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'post_nasal_drip','id' => 'post_nasal_drip','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_post_nasal_drip(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="apnea" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Apnea')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'apnea','id' => 'apnea','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_apnea(),'separator' => ""), $this);?>

                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class=""><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Breast')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</legend>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="breast_mass" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Breast Mass')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'breast_mass','id' => 'breast_mass','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_breast_mass(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="abnormal_mammogram" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Abnormal Mammogram')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'abnormal_mammogram','id' => 'abnormal_mammogram','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_abnormal_mammogram(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="breast_discharge" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Discharge')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'breast_discharge','id' => 'breast_discharge','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_breast_discharge(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="biopsy" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Biopsy')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
                                <?php echo smarty_function_html_radios(array('name' => 'biopsy','id' => 'biopsy','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_biopsy(),'separator' => ""), $this);?>

                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class=""><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Respiratory')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</legend>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="cough" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Cough')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'cough','id' => 'cough','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_cough(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="wheezing" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Wheezing')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'wheezing','id' => 'wheezing','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_wheezing(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="copd" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='COPD')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'copd','id' => 'copd','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_copd(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="sputum" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Sputum')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'sputum','id' => 'sputum','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_sputum(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="hemoptsyis" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Hemoptysis')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'hemoptsyis','id' => 'hemoptsyis','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_hemoptsyis(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="shortness_of_breath" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Shortness of Breath')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'shortness_of_breath','id' => 'shortness_of_breath','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_shortness_of_breath(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="asthma" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Asthma')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'asthma','id' => 'asthma','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_asthma(),'separator' => ""), $this);?>

                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class=""><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Cardiovascular')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</legend>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="chest_pain" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Chest Pain')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'chest_pain','id' => 'chest_pain','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_chest_pain(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="pnd" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='PND')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'pnd','id' => 'pnd','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_pnd(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="peripheal" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Peripheral')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'peripheal','id' => 'peripheal','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_peripheal(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="history_murmur" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='History of Heart Murmur')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'history_murmur','id' => 'history_murmur','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_history_murmur(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="palpitation" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Palpitation')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'palpitation','id' => 'palpitation','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_palpitation(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="doe" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='DOE')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'doe','id' => 'doe','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_doe(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="edema" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Edema')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'edema','id' => 'edema','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_edema(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="arrythmia" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Arrythmia')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'arrythmia','id' => 'arrythmia','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_arrythmia(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="syncope" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Syncope')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'syncope','id' => 'syncope','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_syncope(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="orthopnea" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Orthopnea')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'orthopnea','id' => 'orthopnea','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_orthopnea(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="legpain_cramping" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp="Leg Pain/Cramping")) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'legpain_cramping','id' => 'legpain_cramping','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_legpain_cramping(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="heart_problem" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Heart Problem')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'heart_problem','id' => 'heart_problem','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_heart_problem(),'separator' => ""), $this);?>

                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class=""><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Gastrointestinal')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</legend>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="dysphagia" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Dysphagia')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'dysphagia','id' => 'dysphagia','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_dysphagia(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="belching" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Belching')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'belching','id' => 'belching','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_belching(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="vomiting" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Vomiting')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'vomiting','id' => 'vomiting','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_vomiting(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="food_intolerance" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Food Intolerance')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'food_intolerance','id' => 'food_intolerance','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_food_intolerance(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="hematochezia" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Hematochezia')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'hematochezia','id' => 'hematochezia','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_hematochezia(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="constipation" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Constipation')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'constipation','id' => 'constipation','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_constipation(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="heartburn" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Heartburn')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'heartburn','id' => 'heartburn','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_heartburn(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="flatulence" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Flatulence')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'flatulence','id' => 'flatulence','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_flatulence(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="hematemesis" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Hematemesis')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'hematemesis','id' => 'hematemesis','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_hematemesis(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="name="hepatitis" " class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp="H/O Hepatitis")) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'hepatitis','id' => 'hepatitis','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_hepatitis(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="name="changed_bowel" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Changed Bowel')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'changed_bowel','id' => 'changed_bowel','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_changed_bowel(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="bloating" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Bloating')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'bloating','id' => 'bloating','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_bloating(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="nausea" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Nausea')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'nausea','id' => 'nausea','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_nausea(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="gastro_pain" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Pain')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'gastro_pain','id' => 'gastro_pain','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_gastro_pain(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="jaundice" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Jaundice')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'jaundice','id' => 'jaundice','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_jaundice(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="diarrhea" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Diarrhea')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'diarrhea','id' => 'diarrhea','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_diarrhea(),'separator' => ""), $this);?>

                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class=""><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Genitourinary')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
 <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='General')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</legend>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="polyuria" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Polyuria')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'polyuria','id' => 'polyuria','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_polyuria(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="hematuria" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Hematuria')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'hematuria','id' => 'hematuria','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_hematuria(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="incontinence" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Incontinence')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
::</label>
                                <?php echo smarty_function_html_radios(array('name' => 'incontinence','id' => 'incontinence','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_incontinence(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="polydypsia" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Polydypsia')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'polydypsia','id' => 'polydypsia','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_polydypsia(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="frequency" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Frequency')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'frequency','id' => 'frequency','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_frequency(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="renal_stones" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Renal Stones')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'renal_stones','id' => 'renal_stones','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_renal_stones(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="dysuria" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Dysuria')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'dysuria','id' => 'dysuria','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_dysuria(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="urgency" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Urgency')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'urgency','id' => 'urgency','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_urgency(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="utis" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='UTIs')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'utis','id' => 'utis','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_utis(),'separator' => ""), $this);?>

                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class=""><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Genitourinary')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
 <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Male')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</legend>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="hesitancy" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Hesitancy')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'hesitancy','id' => 'hesitancy','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_hesitancy(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="nocturia" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Nocturia')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'nocturia','id' => 'nocturia','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_nocturia(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="dribbling" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Dribbling')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'dribbling','id' => 'dribbling','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_dribbling(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="erections" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Erections')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'erections','id' => 'erections','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_erections(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="stream" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Stream')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'stream','id' => 'stream','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_stream(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="ejaculations" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Ejaculations')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'ejaculations','id' => 'ejaculations','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_ejaculations(),'separator' => ""), $this);?>

                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class=""><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Genitourinary')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
 <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Female')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</legend>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="g" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Female G')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'g','id' => 'g','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_g(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="lc" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Female LC')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'lc','id' => 'lc','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_lc(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="lmp" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='LMP')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'lmp','id' => 'lmp','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_lmp(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="f_symptoms" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Symptoms')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'f_symptoms','id' => 'f_symptoms','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_f_symptoms(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="p" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Female P')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'p','id' => 'p','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_p(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="mearche" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Menarche')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'mearche','id' => 'mearche','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_mearche(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="f_frequency" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Frequency')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'f_frequency','id' => 'f_frequency','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_f_frequency(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="abnormal_hair_growth" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Abnormal Hair Growth')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'abnormal_hair_growth','id' => 'abnormal_hair_growth','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_abnormal_hair_growth(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="ap" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Female AP')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'ap','id' => 'ap','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_ap(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="menopause" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Menopause')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</label>
                                <?php echo smarty_function_html_radios(array('name' => 'menopause','id' => 'menopause','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_menopause(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="f_flow" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Flow')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'f_flow','id' => 'f_flow','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_f_flow(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="f_hirsutism" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp="F/H Female Hirsutism/Striae")) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'f_hirsutism','id' => 'f_hirsutism','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_f_hirsutism(),'separator' => ""), $this);?>

                            </div>
                        </div>
                    </fieldset>
                        <fieldset>
                        <legend class=""><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Musculoskeletal')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</legend>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="joint_pain" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Chronic Joint Pain')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'joint_pain','id' => 'joint_pain','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_joint_pain(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="m_warm" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Warm')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'm_warm','id' => 'm_warm','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_m_warm(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="m_aches" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Aches')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'm_aches','id' => 'm_aches','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_m_aches(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="swelling" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Swelling')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'swelling','id' => 'swelling','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_swelling(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="m_stiffness" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Stiffness')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'm_stiffness','id' => 'm_stiffness','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_m_stiffness(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="fms" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='FMS')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'fms','id' => 'fms','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_fms(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="m_redness" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Redness')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'm_redness','id' => 'm_redness','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_m_redness(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="muscle" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Muscle')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'muscle','id' => 'muscle','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_muscle(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="arthritis" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Arthritis')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'arthritis','id' => 'arthritis','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_arthritis(),'separator' => ""), $this);?>

                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class=""><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Neurologic')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</legend>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="loc" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='LOC')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'loc','id' => 'loc','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_loc(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="tia" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='TIA')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'tia','id' => 'tia','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_tia(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="paralysis" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Paralysis')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'paralysis','id' => 'paralysis','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_paralysis(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="dementia" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Dementia')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'dementia','id' => 'dementia','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_dementia(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="seizures" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Seizures')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'seizures','id' => 'seizures','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_seizures(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="n_numbness" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Numbness')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'n_numbness','id' => 'n_numbness','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_n_numbness(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="intellectual_decline" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Intellectual Decline')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'intellectual_decline','id' => 'intellectual_decline','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_intellectual_decline(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="n_headache" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Headache')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'n_headache','id' => 'n_headache','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_n_headache(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="stroke" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Stroke')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'stroke','id' => 'stroke','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_stroke(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="n_weakness" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Weakness')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'n_weakness','id' => 'n_weakness','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_n_weakness(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="memory_problems" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Memory Problems')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'memory_problems','id' => 'memory_problems','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_memory_problems(),'separator' => ""), $this);?>

                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class=""><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Skin')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</legend>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="s_cancer" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Cancer')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 's_cancer','id' => 's_cancer','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_s_cancer(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="s_other" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Other')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 's_other','id' => 's_other','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_s_other(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="psoriasis" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Psoriasis')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'psoriasis','id' => 'psoriasis','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_psoriasis(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="s_disease" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Disease')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 's_disease','id' => 's_disease','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_s_disease(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="s_acne" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Acne')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 's_acne','id' => 's_acne','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_s_acne(),'separator' => ""), $this);?>

                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class=""><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Psychiatric')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</legend>
                                                <div class="col-sm-4">
                            <div class="form-group">
                                <label for="p_diagnosis" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Psychiatric Diagnosis')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'p_diagnosis','id' => 'p_diagnosis','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_p_diagnosis(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="anxiety" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Anxiety')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'anxiety','id' => 'anxiety','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_anxiety(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="p_medication" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Psychiatric Medication')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'p_medication','id' => 'p_medication','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_p_medication(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="social_difficulties" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Social Difficulties')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'social_difficulties','id' => 'social_difficulties','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_social_difficulties(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="depression" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Depression')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'depression','id' => 'depression','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_depression(),'separator' => ""), $this);?>

                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class=""><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Endocrine')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</legend>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="thyroid_problems" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Thyroid Problems')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'thyroid_problems','id' => 'thyroid_problems','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_thyroid_problems(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="diabetes" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Diabetes')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'diabetes','id' => 'diabetes','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_diabetes(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="abnormal_blood" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Abnormal Blood Test')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'abnormal_blood','id' => 'abnormal_blood','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_abnormal_blood(),'separator' => ""), $this);?>

                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class=""><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Hematologic')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
/<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Allergic')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
/<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Immunologic')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</legend>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="anemia" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Anemia')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'anemia','id' => 'anemia','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_anemia(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="allergies" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Allergies')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'allergies','id' => 'allergies','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_allergies(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="hai_status" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='HAI Status')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'hai_status','id' => 'hai_status','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_hai_status(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="fh_blood_problems" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp="F/H Blood Problems")) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'fh_blood_problems','id' => 'fh_blood_problems','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_fh_blood_problems(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="frequent_illness" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Frequent Illness')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'frequent_illness','id' => 'frequent_illness','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_frequent_illness(),'separator' => ""), $this);?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="bleeding_problems" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Bleeding Problems')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'bleeding_problems','id' => 'bleeding_problems','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_bleeding_problems(),'separator' => ""), $this);?>

                            </div>
                            <div class="form-group">
                                <label for="hiv" class="control-label col-sm-6"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='HIV')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</label>
                                <?php echo smarty_function_html_radios(array('name' => 'hiv','id' => 'hiv','options' => $this->_tpl_vars['form']->get_options(),'selected' => $this->_tpl_vars['form']->get_hiv(),'separator' => ""), $this);?>

                            </div>
                        </div>

                    </fieldset>
                    <div class="form-group clearfix">
                        <div class="col-sm-12 col-sm-offset-2 position-override">
                            <div class="btn-group oe-opt-btn-group-pinch" role="group">
                                <button type="submit" class="btn btn-default btn-save" onclick="top.restoreSession();" name="Submit"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Save')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</button>
                                <button type="button" class="btn btn-link btn-cancel oe-opt-btn-separate-left" onclick="top.restoreSession(); location.href='<?php echo $this->_tpl_vars['DONT_SAVE_LINK']; ?>
';"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Cancel')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</button>
                            </div>
                            <input type="hidden" name="id" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['form']->get_id())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
                            <input type="hidden" name="pid" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['form']->get_pid())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
                            <input type="hidden" name="process" value="true">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>