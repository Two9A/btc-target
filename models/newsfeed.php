<?php
/**
 * Pagination example: News feed model
 *
 * Represents a feed of news as an array of elements.
 *
 * @author Imran Nazar <tf@imrannazar.com>
 */

class NewsFeedModel implements Countable, ArrayAccess
{
    private $feed = array();

    /**
     * Model constructor
     * Will read feed from config's "feed_link" if not given.
     * @param [feed_link] string Filename of feed to use
     * @throws bsException if fetch or parsing failed
     */
    public function __construct($feed_link = null)
    {
        if (!$feed_link) {
            $feed_link = bsFactory::get('config')->feed_link;
        }
        $feed_json = file_get_contents($feed_link);

        if (!$feed_json) {
            throw new bsException('Unable to fetch news feed');
        }

        // The feed will sometimes have a stray semicolon on the end
        $feed_json = trim($feed_json, ';');

        // On occasion, there'll be double-encoded apostrophes in the
        // feed. If parsing fails, strip 'em and try again
        $feed = json_decode($feed_json, true);
        if (!$feed) {
            $feed_json = strtr($feed_json, array("\\'" => "'"));
            $feed = json_decode($feed_json, true);

            if (!$feed) {
                throw new bsException('Unable to parse news feed');
            }
        }

        // Validate that we have a feed of the right general format
        if (!isset($feed['entries'])) {
            throw new bsException('Invalid news feed format');
        }

        if (!count($feed['entries'])) {
            throw new bsException('No entries in the news feed');
        }

        foreach (array('headline', 'prompt', 'isBreaking') as $field) {
            if (!isset($feed['entries'][0][$field])) {
                throw new bsException('Expected field missing from the news feed: '.$field);
            }
        }

        $this->feed = $feed['entries'];
    }

    /**
     * Countable interface: How many entries in the feed
     * @return int Number of entries
     */
    public function count()
    {
        return count($this->feed);
    }

    /**
     * ArrayAccess interface: Check if an offset is in the feed
     * @param offs int Index of the offset
     * @return bool
     */
    public function offsetExists($offs)
    {
        return isset($this->feed[$offs]);
    }

    /**
     * ArrayAccess interface: Set a feed entry (unsupported)
     * @param offs int Index of the offset to set
     * @param val mixed Value to give to the offset
     * @throws bsException always
     */
    public function offsetSet($offs, $val)
    {
        throw new bsException('Attempt to overwrite a news feed entry');
    }

    /**
     * ArrayAccess interface: Unset a feed entry (unsupported)
     * @param offs int Index of the offset to unset
     * @throws bsException always
     */
    public function offsetUnset($offs)
    {
        throw new bsException('Attempt to remove a news feed entry');
    }

    /**
     * ArrayAccess interface: Retrieve an entry
     * @param offs int Index of the entry to retrieve
     * @return array Entry details, or empty array if out of range
     */
    public function offsetGet($offs)
    {
        return isset($this->feed[$offs]) ? $this->feed[$offs] : array();
    }
}

