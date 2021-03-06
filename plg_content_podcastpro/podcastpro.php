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

if (version_compare(JVERSION, '1.6', '>')) {
	JFactory::getApplication()->registerEvent( 'onContentPrepare', 'plgContentPodcast_onPrepareContent' );
} else {
	JFactory::getApplication()->registerEvent( 'onPrepareContent', 'plgContentPodcast' );
}

function plgContentPodcast_onPrepareContent($context, &$article, &$params, $limitstart=0) {
	return plgContentPodcast($article, $params);
}

function plgContentPodcast( &$row, &$params, $page=0 )
{
	// Performance check: don't go any farther if we don't have an {enclose ...} tag
	if ( JString::strpos( $row->text, 'enclose' ) === false && JString::strpos( $row->text, 'player' ) === false) {
		return true;
	}

	jimport('joomla.filesystem.file');

	preg_match_all( '/\{(enclose|player) (.*)\}/i' , $row->text, $matches );

	$podcastParams =& JComponentHelper::getParams('com_podcastpro');

	foreach ($matches[2] as $id => $podcast) {

		/*
		 * $podcast contents:
		 * $podcast[0] filename (required)
		 * $podcast[1] file length in bytes
		 * $podcast[2] file mime type
		 *
		 * We're only interested in $podcast[0] here
		 */
		$enclose = explode(' ', $podcast);

		$player = new PodcastPlayer($podcastParams, $enclose, $row->title);

		$row->text = JString::str_ireplace($matches[0][$id], $player->generate(), $row->text);
	}

	return true;
}

class PodcastPlayer
{
	private $playerType = null;
	private $enclose = null;
	private $fileURL = null;
	private $title = null;
	private $podcastParams = null;
	private $validTypes = array('links', 'player', 'html', 'QTplayer');
	private $fileTypes = array (
		'asf' => 'video/asf',
		'asx' => 'video/asf',
		'avi' => 'video/avi',
		'm4a' => 'audio/x-m4a',
		'm4v' => 'video/x-m4v',
		'mov' => 'video/quicktime',
		'mp3' => 'audio/mpeg',
		'mpe' => 'video/mpeg',
		'mpeg' => 'video/mpeg',
		'mpg' => 'video/mpeg',
		'ogg' => 'audio/ogg',
		'qt' => 'video/quicktime',
		'ra' => 'audio/x-realaudio',
		'ram' => 'audio/x-realaudio',
		'wav' => 'audio/wav',
		'wax' => 'video/asf',
		'wma' => 'audio/wma',
		'wmv' => 'video/wmv',
		'wmx' => 'video/asf',
	);

	function __construct(&$podcastParams, $enclose, $title)
	{
		$this->podcastParams =& $podcastParams;
		$playerType = $this->podcastParams->get('linkhandling', 'player');

		if (in_array($playerType, $this->validTypes)) {
			$this->playerType = $playerType;
		} else {
			$this->playerType = 'player';
		}

		$this->fileURL = $this->determineURL($enclose[0]);
		$this->title = $title;
		$this->enclose = $enclose;
	}

	public function generate()
	{
		$func = $this->playerType;

		return $this->$func();
	}

	private function determineURL($filename)
	{
		$mediapath = $this->podcastParams->get('mediapath', 'media/com_podcastpro/episodes');

		// If we have a full URL, stop.
		// Otherwise, see if the file is the normal media path and build URL
		// Else, just assume Joomla root
		if (!preg_match('/^https?:\/\//', $filename)) {

			$fullPath = JPATH_BASE . '/' . $mediapath . '/' . $filename;

			if (JFile::exists($fullPath)) {
				$filename = JURI::base() . $mediapath . '/' . $filename;
			} else {
				$filename = JURI::base() . $filename;
			}

		}

		return $filename;
	}

	private function links()
	{
		$kbox = $this->podcastParams->get('kbox', '1') ? ' rel="mediabox"' : '';
		return '<a href="' . $this->fileURL . $kbox . '>' . htmlspecialchars($this->podcastParams->get('linktitle', 'Click to Listen Now')) . '</a>';
	}

	private function player()
	{
		$width = $this->podcastParams->get( 'playerwidth', 400);
		$height = $this->podcastParams->get( 'playerheight', 15);

		$playerURL = JURI::base() . 'plugins/content/podcastpro/xspf_player_slim.swf';

		return '<object type="application/x-shockwave-flash" width="' . $width . '" height="' . $height . '" data="' . $playerURL . '?song_url=' . $this->fileURL . '&song_title=' . $this->title . '&player_title=' . $this->title . '"><param name="movie" value="' . $playerURL . '?song_url=' . $this->fileURL . '&song_title=' . $this->title . '&player_title=' . $this->title . '" /></object>';
	}

	private function html()
	{
		$linkcode = $this->podcastParams->get('linkcode', '');
		return preg_replace('/\{filename\}/', $this->fileURL, $linkcode);
	}

	private function QTplayer()
	{
		$ext = substr($this->enclose[0], strlen($this->enclose[0]) - 3);

		$width = $this->podcastParams->get( 'playerwidth', 320);
		$height = $this->podcastParams->get( 'playerheight', 240);

		$player = '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="' . $width . '" height="' . $height . '" codebase="http://www.apple.com/qtactivex/qtplugin.cab">'
		. '<param name="src" value="' . $this->fileURL . '" />'
		. '<param name="href" value="' . $this->fileURL . '" />'
		. '<param name="scale" value="aspect" />'
		. '<param name="controller" value="true" />'
		. '<param name="autoplay" value="false" />'
		. '<param name="bgcolor" value="000000" />'
		. '<param name="pluginspage" value="http://www.apple.com/quicktime/download/" />'
		. '<embed src="' . $this->fileURL . '" width="' . $width . '" height="' . $height . '" scale="aspect" cache="true" bgcolor="000000" autoplay="false" controller="true" src="' . $this->fileURL .'" type="' . $this->fileTypes[$ext] . '" pluginspage="http://www.apple.com/quicktime/download/"></embed>'
		. '</object>';

		return $player;
	}
}
