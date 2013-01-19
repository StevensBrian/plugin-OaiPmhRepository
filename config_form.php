<?php 
/**
 * Config form include
 *
 * Included in the configuration page for the plugin to change settings.
 *
 * @package OaiPmhRepository
 * @author John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2009 John Flatness, Yu-Hsun Lin
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
?>

<div class="field">
    <label for="oaipmh_repository_name"><?php echo __('Repository name'); ?></label>
    <?php echo get_view()->formText('oaipmh_repository_name', $repoName);?>
    <p class="explanation"><?php echo __('Name for this OAI-PMH repository.'); ?></p>
</div>
<div class="field">
    <label for="oaipmh_repository_namespace_id"><?php echo __('Namespace identifier'); ?></label>
    <?php echo get_view()->formText('oaipmh_repository_namespace_id', $namespaceID);?>
    <p class="explanation"><?php echo __('This will be used to form globally unique IDs for the exposed metadata items. This value is required to be a domain name you have registered.  Using other values will generate invalid identifiers.'); ?></p>
</div>
<div class="field">
    <label for="oaipmh_repository_expose_files"><?php echo __('Expose files'); ?></label>
    <?php echo get_view()->formCheckbox('oaipmh_repository_expose_files', $exposeFiles, null, 
        array('checked' => '1', 'unChecked' => '0'));?>
    <p class="explanation"><?php echo __('Whether the plugin should include identifiers for the files associated with items.  This provides harvesters with direct access to files.'); ?></p>
</div>
<div class="field">
    <label for="oaipmh_repository_expose_files"><?php echo __('OAI-PMH Repository'); ?></label>
    <p class="explanation"><?php echo __('Harvesters can access metadata from this site at'); ?> <a href="<?php echo OAI_PMH_BASE_URL ?>"><?php echo OAI_PMH_BASE_URL ?></a></p>.
</div>
