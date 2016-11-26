<?php
/**
 * Extension to provide discussion threading similar to a listserv archive
 *
 * @file
 * @author Jack D. Pond <jack.pond@psitex.com>
 * @ingroup Extensions
 * @copyright  2007 Jack D. pond
 * @url http://www.mediawiki.org/wiki/Manual:Extensions
 * @licence GNU General Public Licence 2.0 or later
 */

if (!defined('MEDIAWIKI')) die('Not an entry point.');

# Internationalisation file
$wgMessagesDirs['DiscussionThreading'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['DiscussionThreading'] =  __DIR__ . '/DiscussionThreading.i18n.php';
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'DiscussionThreading',
	'author' => array( 'Jack D. Pond' , 'Daniel Brice' ),
	'version' => '1.5.0',
	'url' => 'https://www.mediawiki.org/wiki/Extension:DiscussionThreading',
	'descriptionmsg' => 'discussionthreading-desc',
);

/**
 * Set up hooks for discussion threading
 *
 * @param $wgSectionThreadingOn bool global logical variable to activate threading
 */
global $wgSectionThreadingOn;
$wgSectionThreadingOn = true;

$wgAutoloadClasses['DiscussionThreading'] = __DIR__ . '/DiscussionThreading.class.php';

$wgHooks['EditPage::showEditForm:initial'][] = 'DiscussionThreading::efDiscussionThread';
$wgHooks['EditPage::attemptSave'][] = 'DiscussionThreading::onAttemptSave';
$wgHooks['EditPage::showEditForm:initial'][] = 'DiscussionThreading::efDiscussionThreadEdit';
$wgHooks['AlternateEdit'][] = 'DiscussionThreading::efDiscussionThreadEdit';
$wgHooks['DoEditSectionLink'][] = 'DiscussionThreading::onDoEditSectionLink';
