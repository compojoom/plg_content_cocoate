<?php
/**
 * @author Daniel Dimitrov
 *
 * @copyright  Copyright (C) 2008 - 2012 compojoom.com, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

class plgContentCocoate extends JPlugin
{

    /**
     * Plugin that loads module positions within content
     *
     * @param	string	The context of the content being passed to the plugin.
     * @param	object	The article object.  Note $article->text is also available
     * @param	object	The article params
     * @param	int		The 'page' number
     * @return bool
     */
    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {
        // simple performance check to determine whether bot should process further
        if (strpos($article->text, 'cocoate') === false && strpos($article->text, 'cocoate') === false) {
            return true;
        }

        // load the language file
        $jlang =& JFactory::getLanguage();
        $jlang->load('plg_content_cocoate.sys', JPATH_ADMINISTRATOR, 'en-GB', true);
        $jlang->load('plg_content_cocoate.sys', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
        $jlang->load('plg_content_cocoate.sys', JPATH_ADMINISTRATOR, null, true);

        // expression to search for (positions)
        $regex		= '/{cocoate\s+(.*?)}/i';

        // enable caching
        $cache = JFactory::getCache('plg_content_cocoate', 'output');
        $cache->setCaching(true);

        // Find all instances of plugin and put in $matches for cocoate
        // $matches[0] is full pattern match, $matches[1] is the chapter
        preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);
        // No matches, skip this
        if ($matches) {
            foreach ($matches as $match) {
                $content[] = $cache->get($match[1], 'plg_cocoate');

                if(!$content[0]) {
                    $chapter = simplexml_load_file('http://cocoate.com/chapter/'.$match[1]);

                    if($chapter) {
                        $content[] = $chapter->node->Content->__toString();
                        $content[] = $chapter->node->Sponsors;
                        $content[] = '<div class="source"><a href="http://cocoate.com/node/'.$match[1].'">'.JText::_('PLG_CONTENT_COCOATE_READ_CHAPTER_ON').'</div>';
                        $cache->store(implode('', $content), $match[1], 'plg_cocoate');
                    } else {
                        $content[] = '<div class="error">'.JText::_('PLG_CONTENT_COCOATE_COULD_NOT_FETCH_CONTENT').'</div>';
                    }
                 }
                // replace match
                $article->text = preg_replace("|$match[0]|", addcslashes(implode('',$content), '\\$'), $article->text, 1);
            }
        }

    }
}