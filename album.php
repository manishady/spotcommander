<?php

/*

Copyright 2013 Ole Jon Bjørkum

This file is part of SpotCommander.

SpotCommander is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

SpotCommander is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with SpotCommander.  If not, see <http://www.gnu.org/licenses/>.

*/

require_once('main.php');

$browse_uri = $_GET['uri'];
$album_uri = (get_uri_type($browse_uri) == 'track') ? lookup_track_album($browse_uri) : $browse_uri;
$metadata = (empty($album_uri)) ? null : lookup_album($album_uri);

if(empty($metadata))
{
	$activity = array();
	$activity['title'] = 'Error';
	$activity['actions'] = array('icon' => array('Retry', 'reload_32_img_div'), 'keys' => array('actions'), 'values' => array('reload_activity'));

	echo '
		<div id="activity_inner_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Lookup API error. Try again.</div></div>

		</div>
	';
}
else
{
	if(get_uri_type($browse_uri) == 'track') save_track_album($browse_uri, $album_uri);

	$artist = $metadata['album']['artist'];
	$artist_uri = (!empty($metadata['album']['artist-id'])) ? $metadata['album']['artist-id'] : '';
	$title = $metadata['album']['name'];
	$tracks = $metadata['album']['tracks'];

	$count = count($tracks);

	$dialog_actions = array();
	$dialog_actions[] = array('text' => 'More by ' . hsc($artist), 'keys' => array('actions', 'string'), 'values' => array('hide_dialog search_spotify', rawurlencode('artist:"' . $artist . '"')));
	$dialog_actions[] = array('text' => 'Play artist', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog play_artist', $artist_uri));
	$dialog_actions[] = array('text' => 'Queue tracks', 'keys' => array('actions', 'uri', 'randomly'), 'values' => array('hide_dialog queue_uris', $album_uri, 'false'));
	$dialog_actions[] = array('text' => 'Queue tracks randomly', 'keys' => array('actions', 'uri', 'randomly'), 'values' => array('hide_dialog queue_uris', $album_uri, 'true'));
	$dialog_actions[] = array('text' => 'Share', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog share_uri', rawurlencode(uri_to_url($album_uri))));

	$activity = array();
	$activity['title'] = hsc($title);
	$activity['cover_art'] = $album_uri;
	$activity['actions'] = array('icon' => array('More', 'overflow_32_img_div'), 'keys' => array('actions'), 'values' => array('show_activity_overflow_actions'));
	$activity['overflow_actions'][] = array('text' => 'Play', 'keys' => array('actions', 'uri'), 'values' => array('play_uri', $album_uri));
	$activity['overflow_actions'][] = array('text' => 'Play randomly', 'keys' => array('actions', 'uri'), 'values' => array('play_uri_randomly', $album_uri));
	$activity['overflow_actions'][] = array('text' => ucfirst(uri_is_starred($album_uri)), 'keys' => array('actions', 'type', 'artist', 'title', 'uri'), 'values' => array('star_uri', 'album', rawurlencode($artist), rawurlencode($title), $album_uri));
	$activity['overflow_actions'][] = array('text' => 'More...', 'keys' => array('actions', 'dialogactions'), 'values' => array('show_dialog_actions', base64_encode(json_encode($dialog_actions))));

	echo '
		<div id="cover_art_div">
		<div id="cover_art_art_div" class="actions_div" data-actions="resize_cover_art" onclick="void(0)"></div>
		<div id="cover_art_play_div" class="actions_div" data-actions="play_uri" data-uri="' . $album_uri . '" data-highlightclass="opacity_highlight" onclick="void(0)"></div>
		<div id="cover_art_information_div"><div><div>Album by ' . $artist . '</div></div><div><div>' . get_tracks_count($count) . '</div></div></div>
		</div>

		<div id="activity_inner_div" class="below_cover_art_div" data-activitydata="' . base64_encode(json_encode($activity)) . '">

		<div class="divider_div">ALL</div>

		<div class="list_div">
	';

	$initial_results = 20;

	$i = 0;

	foreach($tracks as $track)
	{
		$i++;

		$artist = get_artists($track['artists']);
		$title = $track['name'];
		$uri = $track['href'];

		$dialog_actions = array();
		$dialog_actions[] = array('text' => 'Start track radio', 'keys' => array('actions', 'uri', 'playfirst'), 'values' => array('hide_dialog start_track_radio', $uri, 'true'));
		$dialog_actions[] = array('text' => 'Play artist', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog play_artist', $uri));
		$dialog_actions[] = array('text' => 'Lyrics', 'keys' => array('actions', 'activity', 'subactivity', 'args'), 'values' => array('hide_dialog change_activity', 'lyrics', '', 'artist=' . rawurlencode($artist) . '&amp;title=' . rawurlencode($title)));
		$dialog_actions[] = array('text' => 'Share', 'keys' => array('actions', 'uri'), 'values' => array('hide_dialog share_uri', rawurlencode(uri_to_url($uri))));

		$class = ($i > $initial_results) ? 'hidden_div' : '';

		echo '
			<div class="list_item_div ' . $class . '">
			<div title="' . hsc($artist . ' - ' . $title) . '" class="list_item_main_div actions_div" data-actions="toggle_list_item_actions" data-trackuri="' . $uri . '" data-highlightotherelement="div.list_item_main_corner_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="corner_arrow_dark_grey_highlight" onclick="void(0)">
			<div class="list_item_main_actions_arrow_div"></div>
			<div class="list_item_main_corner_arrow_div"></div>
			<div class="list_item_main_inner_div">
			<div class="list_item_main_inner_icon_div"><div class="img_div img_24_div ' . track_is_playing($uri, 'icon') . '"></div></div>
			<div class="list_item_main_inner_text_div"><div class="list_item_main_inner_text_upper_div ' . track_is_playing($uri, 'text') . '">' . hsc($title) . '</div><div class="list_item_main_inner_text_lower_div">' . hsc($artist) . '</div></div>
			</div>
			</div>
			<div class="list_item_actions_div">
			<div class="list_item_actions_inner_div">
			<div title="Play" class="actions_div" data-actions="play_uri" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" data-highlightotherelement="div.list_item_main_actions_arrow_div" data-highlightotherelementparent="div.list_item_div" data-highlightotherelementclass="up_arrow_dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div play_24_img_div"></div></div>
			<div title="Queue" class="actions_div" data-actions="queue_uri" data-artist="' . rawurlencode($artist) . '" data-title="' . rawurlencode($title) . '" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div queue_24_img_div"></div></div>
			<div title="Star" class="actions_div" data-actions="star_uri" data-type="track" data-artist="' . rawurlencode($artist) . '" data-title="' . rawurlencode($title) . '" data-uri="' . $uri . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div ' . uri_is_starred($uri) . '_24_img_div"></div></div>
			<div title="More by ' . hsc($artist) . '" class="actions_div" data-actions="search_spotify" data-string="' . rawurlencode('artist:"' . $artist . '"') . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div search_24_img_div"></div></div>
			<div title="More" class="actions_div" data-actions="show_dialog_actions" data-dialogactions="' . base64_encode(json_encode($dialog_actions)) . '" data-highlightclass="dark_grey_highlight" onclick="void(0)"><div class="img_div img_24_div overflow_24_img_div"></div></div>
			</div>
			</div>
			</div>
		';
	}

	if(count($tracks) > $initial_results) echo '<div class="show_all_list_items_div actions_div" data-actions="show_all_list_items" data-items="list_item_div" data-highlightclass="light_grey_highlight" onclick="void(0)"><div><div><div class="img_div img_24_div all_24_img_div"></div></div><div>Show all tracks</div></div></div>';

	echo '</div></div>';
}

?>
