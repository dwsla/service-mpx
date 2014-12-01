<?php

namespace Dwsla\Service\Mpx;

/**
 * Misc util functions for MPX
 *
 * @author David Weinraub <david.weinraub@dws.la.>
 */
class Util
{

    /**
     * Extract the raw integer value (mpxId) from an MPX mediaId entry of the 
     * form http://data.media.theplatform.com/media/data/Media/358543427929
     * 
     * @param string $mediaId
     * @return integer
     */
    public static function extractIdFromMediaId($mediaId)
    {
       $comps = explode('/', $mediaId);
       return end($comps);        
    }
    
    /**
     * Build a genuine MPX mediaId from an integer id
     * 
     * @param integer $mediaId
     * @return string
     */
    public static function buildMediaIdFromId($mediaId)
    {
        return 'http://data.media.theplatform.com/media/data/Media/' . $mediaId;
    }
}
