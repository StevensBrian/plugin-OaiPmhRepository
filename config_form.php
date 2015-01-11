<?php 
/**
 * Config form include
 *
 * Included in the configuration page for the plugin to change settings.
 *
 * @package OaiPmhRepository
 * @author John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2009-2014 John Flatness, Yu-Hsun Lin
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

$view = get_view();
?>

<div class="field">
    <div class="two columns alpha">
        <label for="oaipmh_repository_name"><?php echo __('Repository name'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Name for this OAI-PMH repository.'); ?></p>
        <?php echo $view->formText('oaipmh_repository_name', $repoName);?>
    </div>
</div>
<div class="field">
    <div class="two columns alpha">
        <label for="oaipmh_repository_namespace_id"><?php echo __('Namespace identifier'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <?php echo __('This will be used to form globally unique IDs for the exposed metadata items. This value is required to be a domain name you have registered.  Using other values will generate invalid identifiers.'); ?>
        <?php echo $view->formText('oaipmh_repository_namespace_id', $namespaceID);?>
    </div>
</div>
<div class="field">
    <div class="two columns alpha">
        <label for="oaipmh_repository_expose_files"><?php echo __('Expose files'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <?php echo __('Whether the plugin should include identifiers for the files associated with items.  This provides harvesters with direct access to files.'); ?>
        <?php echo $view->formCheckbox('oaipmh_repository_expose_files', $exposeFiles, null, 
            array('checked' => '1', 'unChecked' => '0'));?>
    </div>
</div>
<div class="field">
    <div class="two columns alpha">
        <?php echo $view->formLabel('oaipmh_repository_expose_empty_collections',
            __('Expose empty collections')); ?>
    </div>
    <div class='inputs five columns omega'>
        <p class="explanation">
            <?php echo __('Whether the plugin should expose empty public collections.'); ?>
        </p>
        <?php echo $view->formCheckbox('oaipmh_repository_expose_empty_collections', true,
            array('checked' => (boolean) get_option('oaipmh_repository_expose_empty_collections'))); ?>
    </div>
</div>
<div class="field">
<div class="two columns alpha">
    <label for="oaipmh_repository_expose_files"><?php echo __('OAI-PMH Repository'); ?></label>
</div>
<div class="five columns omega">
<p class="explanation"><?php echo __('Harvesters can access metadata from this site at'); ?> <br/><a href="<?php echo OAI_PMH_BASE_URL ?>"><?php echo OAI_PMH_BASE_URL ?></a></p>.
</div>
</div>
