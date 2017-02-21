<?php
########################################################################
#                                                                       
# LIB_rss                                                               
#                                                                       
# This library provides routines useful when working with RSS feeds     
#                                                                       
#-----------------------------------------------------------------------
# FUNCTIONS                                                             
#                                                                       
# download_parse_rss($target)                                           
#		    Downloads and parses rss data                               
#                                                                       
# display_rss_array($rss_array)                                         
#		    Displays a parsed news feed                                 
#                                                                       
# strip_cdata_tags()                                                    
#           Removes cdata[] tags from strings                           
#                                                                       
#-----------------------------------------------------------------------

/***********************************************************************
download_parse_rss($target)     						                
-------------------------------------------------------------			
DESCRIPTION:															
		Downloads and parses a RSS web site                             
INPUT:																    
		$target                                                         
            The web address of the RSS feed                             
RETURNS:																
		The parsed RSS feed                                             
***********************************************************************/

function download_parse_rss($feed)
    {
    
    # Parse title & copyright notice
    $rss_array['TITLE'] = return_between($feed, "<title>", "</title>", EXCL);
    $rss_array['COPYRIGHT'] = return_between($feed, "<copyright>", "</copyright>", EXCL);

    # Parse the items
    $item_array = parse_array($feed, "<item>", "</item>");
    for($xx=0; $xx<count($item_array); $xx++)
        {
        $rss_array['ITITLE'][$xx] = return_between($item_array[$xx], "<title>", "</title>", EXCL);
        $rss_array['ILINK'][$xx] = return_between($item_array[$xx], "<link>", "</link>", EXCL);
        $rss_array['IDESCRIPTION'][$xx] = return_between($item_array[$xx], "<description>", "</description>", EXCL);
        $rss_array['IPUBDATE'][$xx] = return_between($item_array[$xx], "<pubDate>", "</pubDate>", EXCL);
        }

    return $rss_array;
    }

/***********************************************************************
display_rss_array($rss_array)     						                
-------------------------------------------------------------			
DESCRIPTION:															
		Displays parsed RSS data                                        
INPUT:																    
		$target                                                         
            The web address of the RSS feed                             
RETURNS:																
		Sends results to the display device                             
***********************************************************************/
function display_rss_array($rss_array)
{
    $r = '<table border="0">';
//       <!-- Display the article title and copyright notice -->
    $r.= '<tr><td><font size="+1"><b>'. strip_cdata_tags($rss_array['TITLE']) .'</b></font></td></tr>
        <tr><td>'. strip_cdata_tags($rss_array['COPYRIGHT']) . '</td></tr>';
//        <!-- Display the article descriptions and links -->
        if(isset($rss_array['ITITLE'])){
        for($xx=0; $xx<count($rss_array['ITITLE']); $xx++){
            $r.= '
            <tr>
                <td>
                    <a href="'. strip_cdata_tags($rss_array['ILINK'][$xx]) .'">
                        <b>'. strip_cdata_tags($rss_array['ITITLE'][$xx]) .'</b>
                    </a>
                </td>
            </tr>
            <tr>
                <td>'. strip_cdata_tags($rss_array['IDESCRIPTION'][$xx]) .'</td>
            </tr>
            <tr>
                <td><font size="-1">'. strip_cdata_tags($rss_array['IPUBDATE'][$xx]) .'</font></td>
            </tr>';
          }
        }
    $r .= '</table>';
    return $r;
}

/***********************************************************************
strip_cdata_tags($string)                                               
-------------------------------------------------------------			
DESCRIPTION:															
		Removes CDDATA tags from a string                               
                                                                        
INPUT:																    
		$string                                                         
            Text containing CDDATA tags                                 
RETURNS:																
		Returns a string free of CDDATA tags                            
***********************************************************************/
function strip_cdata_tags($string)
    {
    # Strip XML CDATA characters from all array elements
    $string = str_replace("<![CDATA[", "", $string);
    $string = str_replace("]]>", "", $string);
    return $string;
    }  
