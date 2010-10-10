<?php
	error_reporting(E_ERROR | E_WARNING | E_PARSE);

	require_once "functions.php"; 
	require_once "sessions.php";
	require_once "sanity_check.php";
	require_once "version.php"; 
	require_once "config.php";
	require_once "db-prefs.php";

	$link = db_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);	

	login_sequence($link);

	$dt_add = get_script_dt_add();

	no_cache_incantation();

	header('Content-Type: text/html; charset=utf-8');
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>Tiny Tiny RSS</title>

	<link rel="stylesheet" type="text/css" href="tt-rss.css?<?php echo $dt_add ?>"/>
	<link rel="stylesheet" type="text/css" href="infobox.css?<?php echo $dt_add ?>"/>

	<?php	$user_theme = get_user_theme_path($link);
		if ($user_theme) { ?>
			<link rel="stylesheet" type="text/css" href="<?php echo $user_theme ?>/theme.css?<?php echo $dt_add ?>">
	<?php } ?>

	<?php $user_css_url = get_pref($link, 'USER_STYLESHEET_URL'); ?>
	<?php if ($user_css_url) { ?>
		<link rel="stylesheet" type="text/css" href="<?php echo $user_css_url ?>"/> 
	<?php } ?>

	<link rel="shortcut icon" type="image/png" href="images/favicon.png"/>

	<script type="text/javascript" src="lib/prototype.js"></script>
	<script type="text/javascript" src="lib/scriptaculous/scriptaculous.js?load=effects,dragdrop,controls"></script>
	<script type="text/javascript" charset="utf-8" src="localized_js.php?<?php echo $dt_add ?>"></script>
	<script type="text/javascript" charset="utf-8" src="tt-rss.js?<?php echo $dt_add ?>"></script>
	<script type="text/javascript" charset="utf-8" src="functions.js?<?php echo $dt_add ?>"></script>
	<script type="text/javascript" charset="utf-8" src="feedlist.js?<?php echo $dt_add ?>"></script>
	<script type="text/javascript" charset="utf-8" src="viewfeed.js?<?php echo $dt_add ?>"></script>
	<script type="text/javascript" charset="utf-8" src="offline.js?<?php echo $dt_add ?>"></script>

	<script type="text/javascript" src="gears_init.js"></script>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<script type="text/javascript">
		Event.observe(window, 'load', function() {
			init();
		});
	</script>
</head>

<body id="ttrssMain">

<div id="overlay" style="display : block">
	<div id="overlay_inner">
		<?php echo __("Loading, please wait...") ?>

		<div id="l_progress_o">
			<div id="l_progress_i"></div>
		</div>

	<noscript>
		<p>
		<?php print_error(__("Your browser doesn't support Javascript, which is required
		for this application to function properly. Please check your
		browser settings.")) ?></p>
	</noscript>
	</div>
</div> 

<div id="hotkey_help_overlay" style="display : none" onclick="Element.hide(this)">
	<?php rounded_table_start("hho"); ?>
	<?php include "help/3.php" ?>
	<?php rounded_table_end(); ?>
</div>

<div id="notify" class="notify"><span id="notify_body">&nbsp;</span></div>

<div id="dialog_overlay" style="display : none"> </div>

<ul id="debug_output" style='display : none'><li>&nbsp;</li></ul>

<div id="infoBoxShadow" style="display : none"><div id="infoBox">&nbsp;</div></div>

<div id="cmdline" style="display : none"></div>
<div id="auxDlg" style="display : none"></div>

<div id="errorBoxShadow" style="display : none">
	<div id="errorBox">
	<div id="xebTitle"><?php echo __('Fatal Exception') ?></div><div id="xebContent">&nbsp;</div>
		<div id="xebBtn" align='center'>
			<button onclick="closeErrorBox()"><?php echo __('Close this window') ?></button>
		</div>
	</div>
</div>

<div id="header">
	<div class="topLinks" id="topLinks">

	<span id="topLinksOnline">

	<?php if (!SINGLE_USER_MODE) { ?>
			<?php echo __('Hello,') ?> <b><?php echo $_SESSION["name"] ?></b> |
	<?php } ?>
	<a href="prefs.php"><?php echo __('Preferences') ?></a>

	<?php if (defined('FEEDBACK_URL') && FEEDBACK_URL) { ?>
		| <a target="_blank" class="feedback" href="<?php echo FEEDBACK_URL ?>">
				<?php echo __('Comments?') ?></a>
	<?php } ?>

	<?php if (!SINGLE_USER_MODE) { ?>
			| <a href="logout.php"><?php echo __('Logout') ?></a>
	<?php } ?>

	<img id="offlineModePic" 
		onmouseover="enable_selection(false)" 
		onmouseout="enable_selection(true)"
		onclick="toggleOfflineModeInfo()"
		src="images/offline.png" style="display:none"
		width="16" height="16"
		title="<?php echo __('Offline reading') ?>"/>

	<div id="offlineModeDrop" style="display : none">
		<div id="offlineModeSyncMsg">---</div>

		<div class="showWhenSyncing" style="display : none">
			<a href="javascript:offlineDownloadStop()">
			<?php echo __('Cancel synchronization') ?></a></div>
		<div class="hideWhenSyncing">
			<a href="javascript:offlineDownloadStart()">
			<?php echo __('Synchronize') ?></a></div>
		<div class="hideWhenSyncing"><a href="javascript:offlineClearData()">
			<?php echo __('Remove stored data') ?></a></div>
		<div><a href="javascript:gotoOffline()">
			<?php echo __('Go offline') ?></a></div>
	</div>

	<img id="newVersionIcon" style="display:none;" onclick="javascript:explainError(2)" 
		width="13" height="13" 
		src="<?php echo theme_image($link, 'images/new_version.png') ?>"
		title="<?php echo __('New version of Tiny Tiny RSS is available!') ?>" 
		alt="new_version_icon"/>

	</span>

	<span id="topLinksOffline" style="display : none">
		<img id="restartOnlinePic" src="images/online.png" 
			height="13" width="13" onclick="gotoOnline()" title="<?php echo __('Go online') ?>"/>
	</span>

	</div>

	<img src="<?php echo theme_image($link, 'images/ttrss_logo.png') ?>" alt="Tiny Tiny RSS"/>	
</div>

<div id="feeds-holder">
	<div id="dispSwitch"> 
		<a id="dispSwitchPrompt" 
			href="javascript:toggleTags()"><?php echo __("tag cloud") ?></a>
	</div>
	<div id="feeds-frame">&nbsp;</div>
</div>

<div id="toolbar">

		<div class="actionChooser">
			<select id="quickMenuChooser" onchange="quickMenuChange()">
					<option value="qmcDefault" selected="selected"><?php echo __('Actions...') ?></option>
					<option value="qmcSearch"><?php echo __('Search...') ?></option>
					<optgroup label="<?php echo __('Feed actions:') ?>">
					<option value="qmcAddFeed"><?php echo __('Subscribe to feed...') ?></option>
					<option value="qmcEditFeed"><?php echo __('Edit this feed...') ?></option>
					<option value="qmcRescoreFeed"><?php echo __('Rescore feed') ?></option>
					<option value="qmcCatchupFeed"><?php echo __('Mark as read') ?></option>
					<option value="qmcRemoveFeed"><?php echo __('Unsubscribe') ?></option>
					</optgroup>
					<optgroup label="<?php echo __('All feeds:') ?>">
					<option value="qmcCatchupAll"><?php echo __('Mark as read') ?></option>
					<option value="qmcShowOnlyUnread"><?php echo __('(Un)hide read feeds') ?></option>
					</optgroup>
					<optgroup label="<?php echo __('Categories:') ?>">

					<option value="qmcToggleReorder"><?php echo __('Toggle reordering mode') ?></option>
					<option value="qmcResetCats"><?php echo __('Reset order') ?></option>
					</optgroup>

					<optgroup label="<?php echo __('Other actions:') ?>">

					<option value="qmcAddLabel"><?php echo __('Create label...') ?></option>
					<option value="qmcAddFilter"><?php echo __('Create filter...') ?></option>
					<option value="qmcResetUI"><?php echo __('Reset UI layout') ?></option>
					<option value="qmcHKhelp"><?php echo __('Keyboard shortcuts help') ?></option>
					</optgroup>

			</select>
		</div>

		<form id="main_toolbar_form" action="" onsubmit='return false'>

		<button id="collapse_feeds_btn" onclick="collapse_feedlist()"
			title="<?php echo __('Collapse feedlist') ?>" style="display : none">
			&lt;&lt;</button>

		<select name="view_mode" title="<?php echo __('Show articles') ?>" 
				onchange="viewModeChanged()">
			<option selected="selected" value="adaptive"><?php echo __('Adaptive') ?></option>
			<option value="all_articles"><?php echo __('All Articles') ?></option>
			<option value="marked"><?php echo __('Starred') ?></option>
			<option value="unread"><?php echo __('Unread') ?></option>
			<!-- <option value="noscores"><?php echo __('Ignore Scoring') ?></option> -->
			<option value="updated"><?php echo __('Updated') ?></option>
		</select>

		<select title="<?php echo __('Sort articles') ?>" 
				name="order_by" onchange="viewModeChanged()">
			<option selected="selected" value="default"><?php echo __('Default') ?></option>
			<option value="date"><?php echo __('Date') ?></option>
			<option value="title"><?php echo __('Title') ?></option>
			<option value="score"><?php echo __('Score') ?></option>
		</select>

		<button name="update" onclick="return viewCurrentFeed('ForceUpdate')">
			<?php echo __('Update') ?></button>

		</form>

	</div>

<?php if (!get_pref($link, 'COMBINED_DISPLAY_MODE')) { ?>
	<div id="headlines-frame" class="headlines_normal">
		<div class="whiteBox"><?php echo __('No feed selected.') ?></div></div>
	<div id="content-frame">
	<div id="resize-grabber"
		onmouseover="enable_resize(true)" onmouseout="enable_resize(false)"
		title="<?php echo __('Drag me to resize panels') ?>"> 
		<img src="<?php echo theme_image($link, 
			'images/resize_handle_horiz.png') ?>" id="resize-handle" 
			onmouseover="enable_resize(true)" onmouseout="enable_resize(false)"
			alt=""/>
		</div>
	<div id="content-insert">&nbsp;</div>
	<!-- <div class="whiteBox">&nbsp;</div> --> </div>
<?php } else { ?>
	<div id="headlines-frame" class="headlines_cdm">
		<div class="whiteBox"><?php echo __('No feed selected.') ?></div></div>
<?php } ?>

<div id="footer">
	<a href="http://tt-rss.org/">Tiny Tiny RSS</a>
	<?php if (!defined('HIDE_VERSION')) { ?>
		 v<?php echo VERSION ?> 
	<?php } ?>
	&copy; 2005&ndash;<?php echo date('Y') ?> <a href="http://fakecake.org/">Andrew Dolgov</a>
</div>

<?php db_close($link); ?>

</body>
</html>
