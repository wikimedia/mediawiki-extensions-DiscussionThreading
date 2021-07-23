<?php

class DiscussionThreading {

	/**
	 * @param Skin $skin
	 * @param Title $title
	 * @param string $section
	 * @param string $tooltip
	 * @param array $links
	 * @param string $lang
	 *
	 * @return bool
	 */
	public static function onSkinEditSectionLinks( $skin, $title, $section, $tooltip, &$links, $lang ) {
		global $wgSectionThreadingOn;

		if ( $wgSectionThreadingOn && $title->isTalkPage() ) {

			$tooltip = ( $tooltip == '' ) ? '' :
				wfMessage( 'discussionthreading-replysectionhint', $tooltip )->escaped();

			$links['reply'] = [
				'text' => wfMessage( 'discussionthreading-replysection' )->escaped(),
				'attribs' => [
					'title' => $tooltip,
					'class' => 'known'
				],
				'query' => [
					'action' => 'edit',
					'section' => $section,
					'replyto' => 'yes'
				],
				'targetTitle' => $title
			];

			$tooltip = ( $tooltip == '' ) ? '' :
				wfMessage( 'discussionthreading-threadnewsectionhint', $tooltip )->escaped();

			$links['new'] = [
				'text' => wfMessage( 'discussionthreading-threadnewsection' )->escaped(),
				'attribs' => [
					'title' => $tooltip,
					'class' => 'known'
				],
				'query' => [
					'action' => 'edit',
					'section' => 'new'
				],
				'targetTitle' => $title
			];
		}
		return true;
	}

	/**
	 * This function is a hook used to test to see if empty, if so, start a comment
	 *
	 * @param $efform EditPage object.
	 * @return  true
	 */
	public static function efDiscussionThreadEdit( $efform ) {
		global $wgRequest,$wgSectionThreadingOn;

		$efform->replytosection = '';
		$efform->replyadded = false;
		$efform->replytosection = $wgRequest->getVal( 'replyto' );
		if( !$efform->getTitle()->exists() ) {
			if( $wgSectionThreadingOn && $efform->getTitle()->isTalkPage() ) {
				$efform->section = 'new';
			}
		}
		return true;
	}

	/**
	 * Create a new header, one level below the 'replyto' header, add re: to front and tag it with user information
	 *
	 * @param $efform EditPage Object before display
	 * @return bool
	 */
	public static function efDiscussionThread( $efform ) {
		global $wgSectionThreadingOn;

		$wgSectionThreadingOn = isset( $wgSectionThreadingOn ) && $wgSectionThreadingOn;

		if (
			$efform->replytosection != ''
			&& $wgSectionThreadingOn
			&& isset( $efform->replyadded )
			&& !$efform->replyadded
		) {
			$text = $efform->textbox1;
			$matches = array();
			preg_match( "/^(=+)(.+)\\1/mi" ,
				$efform->textbox1 ,
				$matches );
			if( !empty( $matches[2] ) ) {
				preg_match( "/.*(-+)\\1/mi" , $matches[2] , $matchsign );
				if (!empty($matchsign[0]) ){
					$text = $text."\n\n".$matches[1]."=Re: ".trim( $matchsign[0] )." ~~~~".$matches[1]."=";
				} else {
					$text = $text."\n\n".$matches[1]."=Re: ".trim( $matches[2] )." -- ~~~~".$matches[1]."=";
				}
			} else {
				$text = $text." -- ~~~~<br />\n\n";
			}
			// Add an appropriate number of colons (:) to indent the body.
			// Include replace me text, so the user knows where to reply
			$replaceMeText = " Replace this text with your reply";
			$text .= "\n\n".str_repeat( ":" , strlen( $matches[1] )-1 ).$replaceMeText;
			// Insert javascript hook that will select the replace me text
			global $wgOut;
			$wgOut->addScript("<script type=\"text/javascript\">
function efDiscussionThread(){
var ctrl = document.editform.wpTextbox1;
if (ctrl.setSelectionRange) {
	ctrl.focus();
	var end = ctrl.value.length;
	ctrl.setSelectionRange(end-".strlen($replaceMeText).",end-1);
	ctrl.scrollTop = ctrl.scrollHeight;
} else if (ctrl.createTextRange) {
	var range = ctrl.createTextRange();
	range.collapse(false);
	range.moveStart('character', -".strlen($replaceMeText).");
	range.select();
}
}
addOnloadHook(efDiscussionThread);
		</script>"
			);
			$efform->replyadded = true;
			$efform->textbox1 = $text;
		}
		return true;
	}

	/**
	 * When the new header is created from summary in new (+) add comment, just stamp the header as created
	 *
	 * @param $efform EditPage Object before display
	 * @return bool
	 */
	public static function onAttemptSave( $efform ) {
		global $wgSectionThreadingOn;
		$wgSectionThreadingOn = isset( $wgSectionThreadingOn ) && $wgSectionThreadingOn;
		if (
			$efform->section == "new"
			&& $wgSectionThreadingOn
			&& isset( $efform->replyadded )
			&& !$efform->replyadded
		) {
			$efform->summary = $efform->summary." -- ~~~~";
		}
		return true;
	}
}
