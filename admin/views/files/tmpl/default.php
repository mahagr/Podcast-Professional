<?php
 /**
 * Podcast Professional - The Joomla Podcast Manager
 * @package 	Podcast Professional
 * @copyright 	(C) 2010-2011 Kontent Design. All rights reserved.
 * @copyright 	(c) 2005-2008 Joseph L. LeBlanc
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link 		http://extensions.kontentdesign.com
 **/
defined( '_JEXEC' ) or die();

JToolBarHelper::title( JText::_( 'COM_PODCASTPRO_PODCAST_EPISODE_MANAGER' ), 'podcast.png' );

// JToolBarHelper::custom( 'add' , 'podcastadd.png', '', JText::_( 'COM_PODCASTPRO_ADD_NEW_EPISODE'), 0, 0 );

// The parameters button will go away once it's moved to the submenu.
if (version_compare(JVERSION, '1.6', '>')) {
	JToolBarHelper::preferences('com_podcastpro', '550');
}

// This button isn't hooked up yet.
JToolBarHelper::custom( 'upload' , 'podcastfileupload.png', '', JText::_( 'COM_PODCASTPRO_UPLOAD_FILES'), 0, 0 );

$document =& JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'components/com_podcastpro/media/css/podcastpro.css');

$document->addScript(JURI::base() . 'components/com_podcastpro/media/js/files.js');

JHTML::_('behavior.tooltip');
?>

<form action="index.php?option=com_podcastpro" method="post" name="adminForm" id="adminForm">
	<table>
		<tr>
			<td align="left">
				<strong><?php echo JText::_('Filter'); ?></strong>:
				<input type="text" name="search" id="search" value="<?php echo htmlspecialchars($this->lists['search']);?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.getElementById('filter_catid').value='0';this.form.getElementById('filter_state').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php
					echo $this->filter['published'];
					echo $this->filter['metadata'];
				?>
				<strong><?php echo JText::_('COM_PODCASTPRO_EPISODE_DIRECTORY'); ?>:</strong> <?php JText::printf($this->folder); ?>
			</td>
		</tr>
	</table>
	<table class="adminlist">
		<thead>
			<tr>
				<th width="20"><?php echo JText::_('COM_PODCASTPRO_PODCAST_ID'); ?></th>
				<th width="1%">
					<input type="checkbox" name="toggle" value="" title="<?php echo JText::_('COM_PODCASTPRO_CHECK_ALL'); ?>" onclick="<?php echo version_compare(JVERSION, '1.6', '>') ? 'Joomla.checkAll(this)' : 'checkAll('.count($this->data).')' ?>" />
				</th>

				<th class="title">
					<?php echo JHTML::_('grid.sort',  JText::_('COM_PODCASTPRO_FILENAME'), 'filename', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort',  'COM_PODCASTPRO_ORDERING', 'ordering', $this->lists['order_Dir'], $this->lists['order']); ?>
					<?php echo JHtml::_('grid.order',  $this->data, 'filesave.png', 'saveorder'); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',  JText::_('COM_PODCASTPRO_STATUS'), 'published', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th class="title">
					<?php echo JText::_('COM_PODCASTPRO_EDIT_ARTICLE'); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',  JText::_('COM_PODCASTPRO_METADATA'), 'metadata', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th class="title">
					<?php echo JText::_('COM_PODCASTPRO_VIEW_ARTICLE'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('COM_PODCASTPRO_DELETE_FILE'); ?>
				</th>
			</tr>
		</thead>
		<?php
		$k = 0;
		$i = 0;
		foreach($this->data as $file)
		{
			$id = $file->id;
			if(!$id) {
				$editKeyName = 'filename';
				$editKeyValue = $file->filename;
			} else {
				$editKeyName = 'cid';
				$editKeyValue = $id;
			}
			$checked = JHTML::_('grid.id', $i, htmlentities($editKeyValue), false, $editKeyName);
			if($file->published) {
				$viewLink = JRoute::_("../index.php?option=com_content&view=article&id={$file->articleId}");
				$editLink = JRoute::_("index.php?option=com_content&task=edit&cid[]={$file->articleId}");
				$published = "<img src=\"components/com_podcastpro/media/images/icon-16-published.png\" alt=\"" . JText::_('COM_PODCASTPRO_YES') . "\"/>";
			} else {
				$published = "<img src=\"components/com_podcastpro/media/images/icon-16-unpublished.png\" alt=\"" . JText::_('COM_PODCASTPRO_NO') . "\"/>";
			}
			$link = JRoute::_("index.php?option={$this->option}&task=edit&{$editKeyName}[]=" . urlencode($editKeyValue));
			$deleteLink = JRoute::_("index.php?option={$this->option}&task=delete&cid[]=" . urlencode($file->filename).'&'.JUtility::getToken().'=1');
			?>
			<tr class="<?php echo $file->hasSpaces ? 'filespace' : "row$k"; ?>">
				<td class="center">
					<?php echo $i+1; ?>
				</td>
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $file->id); ?>
				</td>
				<td>
					<?php
						if($file->hasSpaces) {
							echo "<span class=\"hasTip\" title=\"" . JText::_('COM_PODCASTPRO_FILENAME_SPECIAL_CHARACTERS') . " :: " . JText::_('COM_PODCASTPRO_FILENAME_SPECIAL_CHARACTERS_DESC') . "\">$file->filename</span>";
						} elseif($file->published) {
							echo "<span class=\"hasTip\" title=\"" . JText::_('COM_PODCASTPRO_NONEW_EPISODE_LABEL') . " :: " . JText::_('COM_PODCASTPRO_NONEW_EPISODE_DESC') . "\">$file->filename</span>";
						} else {
							echo "<a href=\"$link\" class=\"hasTip\" title=\"" . JText::_('COM_PODCASTPRO_NEW_EPISODE_LABEL') . " :: " . JText::_('COM_PODCASTPRO_NEW_EPISODE_DESC') . "\">$file->filename</a>";
						}
					?>
				</td>
				<td width="100" class="center order">
				<?php if ($file->ordering !== false) : ?>
					<?php if ($this->lists['order'] == 'ordering' && $this->lists['order_Dir'] == 'asc') : ?>
						<span><?php echo $this->pagination->orderUpIcon($i, 1, 'orderup', 'COM_PODCASTPRO_MOVE_UP', $file->ordering); ?></span>
						<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, 1, 'orderdown', 'COM_PODCASTPRO_MOVE_DOWN', $file->ordering); ?></span>
					<?php elseif ($this->lists['order'] == 'ordering' && $this->lists['order_Dir'] == 'desc') : ?>
						<span><?php echo $this->pagination->orderUpIcon($i, 1, 'orderdown', 'COM_PODCASTPRO_MOVE_UP', $file->ordering); ?></span>
						<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, 1, 'orderup', 'COM_PODCASTPRO_MOVE_DOWN', $file->ordering); ?></span>
					<?php endif ?>
					<input type="text" name="order[]" size="5" value="<?php echo $file->ordering;?>" class="text-area-order" />
				<?php endif ?>
				</td>
				<td width="8%" class="center">
					<?php echo $published; ?>
				</td>
				<td width="8%" class="center">
					<?php
					// TODO: Add check for if article exists.
						if($file->published) {
							echo "<a href=\"$editLink\"><img src=\"components/com_podcastpro/media/images/icon-16-edit.png\"
								alt=\"" . JText::_('COM_PODCASTPRO_EDIT_ARTICLE') . "\" class=\"hasTip\" title=\"" . JText::_('COM_PODCASTPRO_EDIT_ARTICLE_LABEL') . " :: " . JText::_('COM_PODCASTPRO_EDIT_ARTICLE_DESC') . "\" /></a>";
						} else {
							echo "<img src=\"components/com_podcastpro/media/images/icon-16-noedit.png\" alt=\"" . JText::_('COM_PODCASTPRO_NO_ARTICLE') . "\" class=\"hasTip\" title=\"" . JText::_('COM_PODCASTPRO_NOEDIT_ARTICLE_LABEL') . " :: " . JText::_('COM_PODCASTPRO_NOEDIT_ARTICLE_DESC') . "\"/>";
						}
					 ?>
				</td>
				<td width="8%" class="center">
					<?php
						if($file->hasMetadata) {
							echo "<a href=\"$link\"><img src=\"components/com_podcastpro/media/images/icon-16-metadata.png\"
							alt=\"". JText::_('COM_PODCASTPRO_EDIT_METADATA'). "\" class=\"hasTip\" title=\"" . JText::_('COM_PODCASTPRO_VIEW_METADATA_LABEL') . " :: " . JText::_('COM_PODCASTPRO_VIEW_METADATA_DESC') . "\"/></a>";
						} elseif($file->published) {
							echo "<a href=\"$link\"><img src=\"components/com_podcastpro/media/images/icon-16-nometadata.png\"
							alt=\"". JText::_('COM_PODCASTPRO_EDIT_METADATA'). "\" class=\"hasTip\" title=\"" . JText::_('COM_PODCASTPRO_NO_METADATA_LABEL') . " :: " . JText::_('COM_PODCASTPRO_NO_METADATA_DESC') . "\"/></a>";
						} else {
							echo "<img src=\"components/com_podcastpro/media/images/icon-16-nometadata.png\"
							alt=\"". JText::_('COM_PODCASTPRO_NO_METADATA'). "\" class=\"hasTip\" title=\"" . JText::_('COM_PODCASTPRO_NO_METADATA_LABEL') . " :: " . JText::_('COM_PODCASTPRO_NO_METADATA_DESC') . "\"/>";
						}
					?>
				</td>
				<td width="8%" class="center">
					<?php
						if($file->published) {
							echo "<a href=\"$viewLink\" target=\"_blank\"><img src=\"components/com_podcastpro/media/images/icon-16-article.png\"
								alt=\"" . JText::_('COM_PODCASTPRO_VIEW_ARTICLE') . "\" class=\"hasTip\" title=\"" . JText::_('COM_PODCASTPRO_VIEW_ARTICLE_LABEL') . " :: " . JText::_('COM_PODCASTPRO_VIEW_ARTICLE_DESC') . "\"/></a>";
						} else {
							echo "<img src=\"components/com_podcastpro/media/images/icon-16-noarticle.png\" alt=\"" . JText::_('COM_PODCASTPRO_NO_ARTICLE') . "\" class=\"hasTip\" title=\"" . JText::_('COM_PODCASTPRO_NOVIEW_ARTICLE_LABEL') . " :: " . JText::_('COM_PODCASTPRO_NOVIEW_ARTICLE_DESC') . "\"/>";
						}
					 ?>
				</td>
				<td width="8%" class="center">
					<?php
						echo "<a href=\"$deleteLink\"><img src=\"components/com_podcastpro/media/images/icon-16-delete.png\"
								alt=\"" . JText::_('COM_PODCASTPRO_DELETE_FILE_LABEL') . "\" class=\"hasTip\" title=\"" . JText::_('COM_PODCASTPRO_DELETE_FILE_LABEL') . " :: " . JText::_('COM_PODCASTPRO_DELETE_FILE_DESC') . "\"/></a>";
					?>
				</td>
			</tr>
			<?php
				$k = 1 - $k;
				$i++;
				}
			?>
		<tfoot>
			<tr><td colspan="9"><?php echo $this->pagination->getListFooter(); ?></td></tr>
		</tfoot>
	</table>

	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<?php include_once(JPATH_ADMINISTRATOR."/components/com_podcastpro/footer.php");
