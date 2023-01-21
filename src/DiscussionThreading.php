<?php

class DiscussionThreading {

	/**
	 * @param Skin $skin
	 * @param Title $title
	 * @param string $section
	 * @param string $tooltip
	 * @param array &$links
	 * @param string $lang
	 *
	 * @return bool
	 */
	public static function onSkinEditSectionLinks( $skin, $title, $section, $tooltip, &$links, $lang ) {
		global $wgSectionThreadingOn;

		if ( $wgSectionThreadingOn && $title->isTalkPage() ) {

			// Override some visual editor styles that interfere with this.
			$skin->getContext()->getOutput()->addModules( 'ext.discussionthreading.link' );
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
	 * @param EditPage $efform EditPage object before display.
	 * @return bool
	 */
	public static function efDiscussionThreadEdit( $efform ) {
		global $wgSectionThreadingOn;
		$request = $efform->getContext()->getRequest();
		$efform->replytosection = '';
		$efform->replyadded = false;
		$efform->replytosection = $request->getVal( 'replyto' );
		if ( !$efform->getTitle()->exists() ) {
			if ( $wgSectionThreadingOn && $efform->getTitle()->isTalkPage() ) {
				$efform->section = 'new';
			}
		}
		return true;
	}

	/**
	 * Create a new header, one level below the 'replyto' header, add re: to front and tag it with user information
	 *
	 * @param EditPage $efform EditPage object before display.
	 * @return bool
	 */
	public static function efDiscussionThread( $efform ) {
		global $wgSectionThreadingOn;

		if (
			$efform->replytosection != ''
			&& $wgSectionThreadingOn
			&& isset( $efform->replyadded )
			&& !$efform->replyadded
		) {
			$text = $efform->textbox1;
			$matches = [];
			preg_match( "/^(=+)(.+)\\1/mi",
				$efform->textbox1,
				$matches );
			if ( !empty( $matches[2] ) ) {
				preg_match( "/.*(-+)\\1/mi", $matches[2], $matchsign );
				if ( !empty( $matchsign[0] ) ) {
					$text = $text . "\n\n" . $matches[1] . "=Re: " . trim( $matchsign[0] ) . " ~~~~" . $matches[1] . "=";
				} else {
					$text = $text . "\n\n" . $matches[1] . "=Re: " . trim( $matches[2] ) . " -- ~~~~" . $matches[1] . "=";
				}
			} else {
				$text = $text . " -- ~~~~<br />\n\n";
			}
			// Add an appropriate number of colons (:) to indent the body.
			// Include replace me text, so the user knows where to reply
			// It is important we coordinate the length here with javascript.
			// Hence we use ->plain() instead of ->text() and use user lang not content.
			$replaceMeText = " " . wfMessage( 'discussionthreading-replacetext' )->plain();
			$text .= "\n\n" . str_repeat( ":", strlen( $matches[1] ) - 1 ) . $replaceMeText;
			// Insert javascript hook that will select the replace me text
			$efform->getContext()->getOutput()->addModules( 'ext.discussionthreading.select' );
			$efform->replyadded = true;
			$efform->textbox1 = $text;
		}
		return true;
	}

	/**
	 * When the new header is created from summary in new (+) add comment, just stamp the header as created
	 *
	 * @param EditPage $efform EditPage object before display.
	 * @return bool
	 */
	public static function onAttemptSave( $efform ) {
		global $wgSectionThreadingOn;
		if (
			$efform->section == "new"
			&& $wgSectionThreadingOn
			&& isset( $efform->replyadded )
			&& !$efform->replyadded
		) {
			$efform->sectiontitle = $efform->sectiontitle . " -- ~~~~";
		}
		return true;
	}
}
