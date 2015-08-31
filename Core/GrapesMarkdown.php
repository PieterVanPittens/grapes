<?php

/**
 */
trait MediawikiLinkTrait
{

	/**
	 * Parses a link indicated by `[[`.
	 * @marker [[
	 */
	protected function parseMediawikiLink($markdown)
	{
        if (preg_match('/^\[\[([\w\|]+?)\]\]/', $markdown, $matches)) {
            return [
                ['MediawikiLink', $matches[0]],
                // return the offset of the parsed text
                strlen($matches[0])
            ];
        }
        return [['MediawikiLink', '[['], 2];
	}
	
	protected function renderMediawikiLink($block) {
		$match = $block[1];
		if ($match == "[[") {
			return $match;
		} else {
			$match = str_replace("[[", "", $match);
			$match = str_replace("]]", "", $match);
			$tokens = explode("|", $match);
			$url = "";
			$label = "";
			if (count($tokens) == 1) {
				$url = $match;
				$label = $match;
			} else {
				$url = $tokens[0];
				$label = $tokens[1];
			}
			$url = "#/wiki/$url";
			$html = "<a href=\"" . $url . "\">" . $label . "</a>";
			return $html;
		}
	}
}

/**
 */
trait WikiDirectivesTrait
{

	/**
	 * Parses a wiki directive
	 * @marker <grapes-
	 */
	protected function parseWikiDirective($markdown)
	{
		$pos = strpos($markdown, ">");
		$name = str_replace("<grapes-", "", $markdown);
		$name = str_replace("/", "", $name);
		$name = str_replace(">", "", $name);

		return [['WikiDirective', $name], $pos+1];
	}
	protected function renderWikiDirective($block) {
		$match = $block[1];
		return "<grapes-$match/>";
	}
}



/**
 * add Mediawiki Links
 * [[Link]] or [[Link|Label]]
 */
class GrapesMarkdown extends cebe\markdown\GithubMarkdown {
	use MediawikiLinkTrait;
	use WikiDirectivesTrait;
}

?>