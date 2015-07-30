<?php
/*
Plugin Name: Extended Random Number Generator
Plugin URI: mailto:dev@ribbedtee.com
Description: Adds extended support to original Random Number Generator plug. Generates a random number (e.g. useful to avoid browsers links cache)
Version: 1.1
Author: RTD LLC Development
Author URI: mailto:rtddev@ribbedtee.com
Text Domain: random_number_generator
Domain Path: /languages/
*/
// Commentaires ajoutés ci-dessus pour avoir la traduction de la description dans la page des extensions

// Interdit les appels directs à la page
if (!function_exists("get_option")) {
  echo 'SIG'; // Silence is golden
  die;
}

// Clé par défaut
if ( !defined('DEFAULT_SHORTCODE_TAG') )
	define( 'DEFAULT_SHORTCODE_TAG', 'random-number');
// Clé utilisée pour le tag à détecter dans les articles/pages/commentaires [random-number]
$random_number_generator_shortcode = get_option('random_number_generator_shortcode');

// Menu parent par défaut : Réglages
if ( !defined('DEFAULT_PARENT_MENU') )
	define( 'DEFAULT_PARENT_MENU', 'options-general.php');
// Menu parent utilisé pour afficher le menu de la page d'options
$random_number_generator_parent_menu = get_option('random_number_generator_parent_menu');

// On valide que la clé et le menu sont corrects
validate_data($random_number_generator_from, $random_number_generator_to, $random_number_generator_format, $random_number_generator_shortcode, $random_number_generator_parent_menu);

// Charge le fichier de traduction s'il existe
load_plugin_textdomain('random_number_generator', '', plugin_basename(dirname(__FILE__).'/languages'));

/**
 * Fonction qui valide les paramètres avant de les renvoyer par référence
 * @param integer &$random_number_generator_from        Valeur minimale
 * @param integer &$random_number_generator_to          Valeur maximale
 * @param string  &$random_number_generator_format      Format utilisé pour l'affichage
 * @param string  &$random_number_generator_shortcode   Tag reconnu
 * @param string  &$random_number_generator_parent_menu Menu parent
 */
function validate_data(&$random_number_generator_from, &$random_number_generator_to, &$random_number_generator_format, &$random_number_generator_shortcode, &$random_number_generator_parent_menu = DEFAULT_PARENT_MENU) {
	// Validation de la cohérence des valeurs
	if (($random_number_generator_from == $random_number_generator_to) || ($random_number_generator_from > $random_number_generator_to)) {
		$random_number_generator_from = 0;
		$random_number_generator_to   = mt_getrandmax();
	}
	if (($random_number_generator_format == null) || (trim($random_number_generator_format) == '')) {
		$random_number_generator_format = "%x";
	}
	if (($random_number_generator_shortcode == null) || (trim($random_number_generator_shortcode) == '')) {
		$random_number_generator_shortcode = DEFAULT_SHORTCODE_TAG;
	}
	if (($random_number_generator_parent_menu == null) || (trim($random_number_generator_parent_menu) == '')) {
		$random_number_generator_parent_menu = DEFAULT_PARENT_MENU;
	}
	// Si on est sur les pages d'administration, on valide que la page de menu existe réellement
	if ( defined($menu) ) {
		$random_number_generator_parent_menu_found = false;
		foreach( $menu as $menuId => $menuItem ) {
			if ($menuItem[2] != $random_number_generator_parent_menu) {
				continue;
			}
			// La page du menu a été trouvée
			$random_number_generator_parent_menu_found = true;
			break;
		}
		// Si le menu n'a pas été trouvé, on met la page par défaut
		if (!$random_number_generator_parent_menu_found) {
			$random_number_generator_parent_menu = DEFAULT_PARENT_MENU;
		}
	}
} // Fin validate_data

/**
 * Fonction appelée à l'activation de l'extension
 */
function random_number_generator_install() {
	// Ajout des valeurs par défaut
  add_option('random_number_generator_from', 0);
  add_option('random_number_generator_to', mt_getrandmax());
  add_option('random_number_generator_format', "%x");
  add_option('random_number_generator_shortcode', DEFAULT_SHORTCODE_TAG);
  add_option('random_number_generator_parent_menu', DEFAULT_PARENT_MENU);
} // Fin random_number_generator_install

/**
 * Fonction qui génère une option du menu
 * @param string  &$menuPage La page liée au menu
 * @param string  &$menuCaption Le libellé du menu
 */
function random_number_generator_generate_selected_option($menuPage, $menuCaption) {
	global $random_number_generator_parent_menu;
	$random_number_generator_option = "<option value=\"$menuPage\"";
  if ($menuPage == $random_number_generator_parent_menu) {
    $random_number_generator_option .= ' selected="selected"';
  }
  $random_number_generator_option .= ">$menuCaption</option>";

  echo $random_number_generator_option;
} // Fin random_number_generator_generate_selected_option

/**
 * Fonction affichant la page de réglage des options
 */
function random_number_generator_optionsPage() {
	global $random_number_generator_shortcode, $random_number_generator_parent_menu, $menu;
	// Récupération des paramètres depuis les options de WP
	$random_number_generator_from      = get_option('random_number_generator_from');
	$random_number_generator_to        = get_option('random_number_generator_to');
	$random_number_generator_format    = get_option('random_number_generator_format');
	validate_data($random_number_generator_from, $random_number_generator_to, $random_number_generator_format, $random_number_generator_shortcode, $random_number_generator_parent_menu);
	
	// Récupération des menus présents
	$parentMenu = array();
	// Boucle sur l'ensemble des menus parent
	foreach( $menu as $menuId => $menuItem ) {
		// On ne prend pas les séparateurs de menu
		if (empty($menuItem[2]) || (strpos($menuItem[4], 'wp-menu-separator') !== false)) {
			continue;
		}
		// On ne prend pas les menus personnalisés
		if (!empty($menuItem[3])) {
			continue;
		}
		// Cas particuliers : Commentaires et Extensions : Suppression du nombre final
		if (($menuItem[2] == 'edit-comments.php') || ($menuItem[2] == 'plugins.php')) {
			// Supprime les tags HTML (Si vous avez l'expression régulière qui va bien pour le faire en une ligne, je suis preneur ;o)
			$menuItem[0] = strip_tags($menuItem[0]);
			// Supprime le nombre final
			$menuItem[0] = str_replace(strrchr($menuItem[0], ' '), '', $menuItem[0]);
		}
		// On mémorise le libellé et la page associée
		$parentMenu[$menuItem[2]] = $menuItem[0];
	}
?>
<div class="wrap">
	<div id="icon-<?php /* Icone en fonction du parent */ echo str_replace(".php", "", $random_number_generator_parent_menu); ?>" class="icon32">
		<br/>
	</div>
 	<h2><?php /*Tr.: Settings page title / %s: Plugin name*/ printf(str_replace('%s', '<strong>%s</strong>', __('%s Options', 'random_number_generator')), 'Extended Random Number Generator'); ?></h2>
	<form method="post" action="options.php">
<?php
    // Après avoir sauvegardé, on peut se retrouver sur une autre page
    if ($random_number_generator_parent_menu != basename($_SERVER['PHP_SELF'])) {
?>
		<div class="error fade" id="message" style="background-color: rgb(255, 251, 204);"><p><strong><?php	_e('You should reopen this page from its new parent menu!', 'random_number_generator'); ?></strong></p></div>
<?php
	}
	wp_nonce_field('update-options');
?>
		<h3><?php _e('General options', 'random_number_generator') ?></h3>
		<p>
			<?php global $random_number_generator_shortcode; printf(__('Simply replace the tag %s by a random number.', 'random_number_generator'), "<code>[$random_number_generator_shortcode]</code>"); ?><br/>
			<span class="description"><?php printf(__('Used to avoid browsers cache by inserting a random number. For instance, %1$s generates %2$s.', 'random_number_generator'), "<code>&lt;a href=\"http://".__('my_url', 'random_number_generator')."?[$random_number_generator_shortcode]\"&gt;</code>", "<code>&lt;a href=\"http://".__('my_url', 'random_number_generator')."?<span style=\"color:black;font-size:medium;font-weight:bold;\">".do_shortcode("[$random_number_generator_shortcode]")."</span>\"&gt;</code>"); ?></span>
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row" style="text-align: right;">
					<?php _e('From:', 'random_number_generator') ?>
				</th>
				<td>
					<input type="text" name="random_number_generator_from" value="<?php echo $random_number_generator_from; ?>" /> 
					<a class="button" title="<?php _e('Dispay more details', 'random_number_generator') ?>" onclick="javascript:random_number_generator_switchVisibility('random_number_generator_from_help');"><?php /*Tr.: Button caption for help*/ _e('?', 'random_number_generator'); ?></a> 
					<span id="random_number_generator_from_help" style="text-align: left; display: none;"> <?php printf(__('Lowest integer value to be returned (default: <code>%d</code>).', 'random_number_generator'), 0); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" style="text-align: right;">
					<?php _e('To:', 'random_number_generator'); ?>
				</th>
				<td>
					<input type="text" name="random_number_generator_to" value="<?php echo $random_number_generator_to; ?>" /> 
					<a class="button" title="<?php _e('Dispay more details', 'random_number_generator') ?>" onclick="javascript:random_number_generator_switchVisibility('random_number_generator_to_help');"><?php _e('?', 'random_number_generator'); ?></a> 
					<span id="random_number_generator_to_help" style="text-align: left; display: none;"> <?php printf(__('Highest integer value to be returned (default: <code>%d</code>).', 'random_number_generator'), mt_getrandmax()); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" style="text-align: right;">
					<?php _e('Format:', 'random_number_generator') ?>
				</th>
				<td>
					<input type="text" name="random_number_generator_format" value="<?php echo $random_number_generator_format; ?>" /> 
					<a class="button" title="<?php _e('Dispay more details', 'random_number_generator') ?>" onclick="javascript:random_number_generator_switchVisibility('random_number_generator_format_help');"><?php _e('?', 'random_number_generator'); ?></a> 
					<span id="random_number_generator_format_help" style="text-align: left; display: none;"> <?php printf(__('Random number produced according to the formatting string above (default: <code>%s</code>).', 'random_number_generator'), '%x'); ?><br/>
					<?php printf(__('See %s documentation for a description of format.', 'random_number_generator'), '<a href="'.__('http://www.php.net/manual/en/function.sprintf.php', 'random_number_generator').'" target"_blank" title="'.__('Visit PHP: sprintf - Manual page', 'random_number_generator').'">sprintf()</a>'); ?></span>
				</td>
			</tr>
		</table>
		<h3><?php _e('Advanced options', 'random_number_generator') ?></h3>
		<p>
			<?php _e('Warning! If you change this value, you have to change all your previous used shortcode tags! Moreover, you have to, of course, not use another already existing tag caption!', 'random_number_generator') ?>
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row" style="text-align: right;">
					<?php _e('Shortcode tag:', 'random_number_generator') ?>
				</th>
				<td>
					<input type="text" name="random_number_generator_shortcode" value="<?php echo $random_number_generator_shortcode; ?>" /> 
					<a class="button" title="<?php _e('Dispay more details', 'random_number_generator') ?>" onclick="javascript:random_number_generator_switchVisibility('random_number_generator_shortcode_help');"><?php /*Tr.: Button caption for help*/ _e('?', 'random_number_generator'); ?></a> 
					<span id="random_number_generator_shortcode_help" style="text-align: left; display: none;"> <?php printf(__('Shortcode tag to replace in the contents (default: <code>%s</code>).', 'random_number_generator'), DEFAULT_SHORTCODE_TAG); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" style="text-align: right;">
					<?php _e('Plugin parent menu:', 'random_number_generator') ?>
				</th>
				<td>
			    <select id="random_number_generator_parent_menu" name="random_number_generator_parent_menu");">
<?php
	// Affichage des différents menus
	foreach( $parentMenu as $menuPage => $menuCaption ) {
		random_number_generator_generate_selected_option($menuPage, $menuCaption);
  }
?>
			    </select>
					<a class="button" title="<?php _e('Dispay more details', 'random_number_generator') ?>" onclick="javascript:random_number_generator_switchVisibility('random_number_generator_parent_menu_help');"><?php /*Tr.: Button caption for help*/ _e('?', 'random_number_generator'); ?></a> <?php _e("Warning! If you change this value, after saving changes, it's better to navigate to the new submenu!", 'random_number_generator'); ?>
					<span id="random_number_generator_parent_menu_help" style="text-align: left; display: none;"> <?php printf(__('Parent menu for this plugin options page (default: <code>%s</code>).', 'random_number_generator'), /* Tr.: Not used - Using WP main domain */__('Settings')); ?></span>
				</td>
			</tr>
		</table>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="random_number_generator_from,random_number_generator_to,random_number_generator_format,random_number_generator_shortcode,random_number_generator_parent_menu" />
		<?php settings_fields( 'random_number_generator' ); ?>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'random_number_generator') ?>" />
		</p>
	</form>
	<p>
		<img src="<?php /* Vous pouvez ajouter vos propres images à votre extension */ echo WP_PLUGIN_URL.'/'.plugin_basename(dirname(__FILE__).'/images/dice'.mt_rand(1, 6).'.png'); ?>" alt="=>" style="vertical-align: bottom;" /> <?php printf(__("As this plugin is initially a sample, you can directly launch the %s from here.", 'random_number_generator'), "<a href=\"plugin-editor.php?file=".plugin_basename(dirname(__FILE__).'/random_number_generator.php')."\">".__('plugin editor','random_number_generator')."</a>"); ?>
	</p>
	<p class="preview">
<?php
	// Je sépare les lignes pour pouvoir ajouter des commentaires aux traducteurs
	_e('Plugin translated by:', 'random_number_generator');
	echo ' <a target="_blank" href="';
	//Tr.: YOUR website URL
	_e('http://blogs.wittwer.fr/whiler/tag/rng/', 'random_number_generator');
	echo '">';
	//Tr.: YOUR name
	_e('Whiler', 'random_number_generator');
	echo ' <a target="_blank" href="';
?>
	</p>
</div>
<?php
} // Fin random_number_generator_optionsPage

/**
 * Fonction ajoutant un lien pour la page des options dans la liste des extensions
 * @param string $links Lien en cours
 * @param string $file Extension en cours
 * @return HTML
 */
function random_number_generator_plugin_action_links($links, $file) {
	global $random_number_generator_parent_menu;
  if ($file == plugin_basename(dirname(__FILE__).'/random_number_generator.php')){
  	//Le commentaire ci-dessous est extrait par Poedit pour être mis dans les commentaires afin d'aider à la traduction
  	//Tr.: Below the plugin name, in the plugin page
	  $settings_link = "<a href='$random_number_generator_parent_menu?page=".plugin_basename(dirname(__FILE__).'/random_number_generator.php')."'>".__('Settings','random_number_generator')."</a>";
	  array_unshift( $links, $settings_link );
  }
  return $links;
} // Fin random_number_generator_plugin_action_links

/**
 * Fonction ajoutant des nouveaux liens sous le commentaire dans la liste des extensions
 * @param string $links Lien en cours
 * @param string $file Extension en cours
 * @return HTML
 */
function random_number_generator_plugin_row_meta($links, $file) {
  if ($file == plugin_basename(dirname(__FILE__).'/random_number_generator.php')){
		$links[] = '<a target="_blank" href="http://wordpress.org/extend/plugins/extended-random-number-generator/changelog/">'.__('Changelog', 'random_number_generator').'</a>';
	}
	return $links;
} // Fin random_number_generator_plugin_row_meta


/**
 * Fonction ajoutant les scripts nécessaires dans les réglages de l'extension
 */
function random_number_generator_scripts() {
  wp_enqueue_script('random_number_generator_script1', WP_PLUGIN_URL.'/'.str_replace(".php",".js",plugin_basename(__FILE__)));
//  wp_enqueue_script('random_number_generator_script2', 'my2ndScript', array('jquery', 'jquery-ui-tabs'));
} // Fin random_number_generator_scripts

/**
 * Fonction ajoutant un lien dans le menu Réglages pour la page des options de l'extension
 */
function random_number_generator_settings_menu() {
	global $random_number_generator_parent_menu;
  if (function_exists('add_submenu_page')) {
  	//Tr.: Settings menu plugin caption
    $random_number_generator_settings_page = add_submenu_page($random_number_generator_parent_menu,__('Ext Rand#Gen.','random_number_generator'), __('Rand#Gen.','random_number_generator'), "edit_plugins", __FILE__, 'random_number_generator_optionsPage');
    // Ajoute les scripts JavaScript utilisés par les pages d'administration de l'extension
    add_action( "admin_print_scripts-$random_number_generator_settings_page", 'random_number_generator_scripts' );
    
    // Après avoir sauvegardé, on peut se retrouver sur une autre page MAIS toujours sur la page d'options...
    if (($random_number_generator_parent_menu != basename($_SERVER['PHP_SELF'])) && (plugin_basename(dirname(__FILE__).'/random_number_generator.php') == $_GET['page'])) {
    	// Duplique la page si on vient de la changer pour que la précédente existe encore temporairement et évite d'avoir une erreur
    	add_submenu_page(basename($_SERVER['PHP_SELF']),__('Rand#Gen.','random_number_generator'), __('Rand#Gen.','random_number_generator'), "edit_plugins", __FILE__, 'random_number_generator_optionsPage');
    }
	}
} // Fin random_number_generator_settings_menu

/**
 * Fonction qui répertorie les paramètres de l'extension et les méthodes de callback(validation)
 */
function register_random_number_generator_settings() { // whitelist options
  register_setting( 'random_number_generator', 'random_number_generator_from',        'intval' ); // Valeur entière uniquement
  register_setting( 'random_number_generator', 'random_number_generator_to',          'intval' );
  register_setting( 'random_number_generator', 'random_number_generator_format',      'wp_filter_nohtml_kses' ); // HTML interdit
  register_setting( 'random_number_generator', 'random_number_generator_shortcode',   'wp_filter_nohtml_kses' );
  register_setting( 'random_number_generator', 'random_number_generator_parent_menu', 'wp_filter_nohtml_kses' );
} // Fin register_random_number_generator_settings

/**
 * Fonction renvoyant un chiffre aléatoire
 * @param string $atts Attributs éventuellement passés dans le tag
 * @param string $content Format à utiliser si encapsulé par le tag
 * @return HTML
 */
function random_number_generator_shortcode_handler($atts, $content) { 
	// $atts    ::= array of attributes
	// $content ::= text within enclosing form of shortcode element
	// $code    ::= the shortcode found, when == callback name
	// examples: [my-shortcode]
	//           [my-shortcode/]
	//           [my-shortcode foo='bar']
	//           [my-shortcode foo='bar'/]
	//           [my-shortcode]content[/my-shortcode]
	//           [my-shortcode foo='bar']content[/my-shortcode]
	
	// [random-number from="2" to="72" format="%b"]%d minutes[/random-number]
	// output:   an integer value between 2 & 72 followed by the word 'minutes'
	// affiche : une valeur entière entre 2 & 72 suivie du mot 'minutes'

	global $random_number_generator_shortcode;
	// Récupération des paramètres depuis les options de WP
	$random_number_generator_from      = get_option('random_number_generator_from');
	$random_number_generator_to        = get_option('random_number_generator_to');
	$random_number_generator_format    = get_option('random_number_generator_format');

	// Récupération des paramètres éventuels avec les options sauvegardées comme valeur par défaut
	extract(shortcode_atts(array('from' => $random_number_generator_from, 'to' => $random_number_generator_to, 'format' => $random_number_generator_format), $atts));

	// Si on a entouré du texte avec la balise, c'est que ce texte est LE format à utiliser
	// Il devrait contenir un champ de spécification pour afficher le nombre aléatoire
	if ($content != "") {
		// On appelle la méthode do_shortcode pour effectuer les modifications éventuelles de tags situés dans le 'content'
		$format = do_shortcode($content);
	}

	// Valide les différentes options
	validate_data($from, $to, $format, $random_number_generator_shortcode);
	// Génération aléatoire
	mt_srand();
	// Renvoie un nombre aléatoire
	$random_number_generator_rc = @sprintf($format, mt_rand($from, $to), mt_rand($from, $to), mt_rand($from, $to), mt_rand($from, $to), mt_rand($from, $to)); // Accepte jusqu'à 5 générations simultanées, avec un format comme par exemple, celui-ci : %d%d%d%d%d
	if ($random_number_generator_rc == "") {
		//Tr.: Returned value when format is invalid
		$random_number_generator_rc = __('Invalid format:','random_number_generator')." '".$format."'";
	}
	return $random_number_generator_rc;
} // Fin random_number_generator_shortcode_handler

// Ajout du hook pour détecter/remplacer le mot-clé lorsqu'il est utilisé
add_shortcode($random_number_generator_shortcode, 'random_number_generator_shortcode_handler');

// Méthode à appeler lors de la génération du menu d'admin / 
add_action('admin_menu', 'random_number_generator_settings_menu');

// Si on est administrateur
if (is_admin()) {
	// Méthode à appeler lors de l'activation de l'extension
  register_activation_hook(__FILE__, "random_number_generator_install");
  // Méthode à appeler lors de l'accès à l'administration
  add_action('admin_init', 'register_random_number_generator_settings' );
  // Méthode à appeler lors de l'affichage des liens d'action sur la page des extensions installées
	add_filter('plugin_action_links', 'random_number_generator_plugin_action_links', 10, 2);
	
	// Ajoute des liens supplémentaires sous le commentaire
	add_filter('plugin_row_meta', 'random_number_generator_plugin_row_meta', 10, 2);
}


//alexvp added 
function random_number_generator_outputfilter($buffer) {
    return do_shortcode($buffer);
}

if(!is_admin())
	ob_start("random_number_generator_outputfilter");

// allow shortcodes inside scripts and iframes
add_filter( 'wp_kses_allowed_html', 'random_number_generator_wp_kses_allowed_html_func' );
function random_number_generator_wp_kses_allowed_html_func( $allowedposttags ) {
    $allowedposttags['script'] = array(
        'src' => true,
    );
    $allowedposttags['iframe'] = array(
        'src' => true
    );
    return $allowedposttags;
}

?>