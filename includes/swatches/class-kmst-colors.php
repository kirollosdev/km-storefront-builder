<?php
/**
 * Color name detection.
 *
 * @package KM_Storefront_Builder
 */

defined( 'ABSPATH' ) || exit;

/**
 * Turns a term name like "Navy Blue", "Black / White" or "أحمر فاتح" into hex colors.
 */
final class KMST_Colors {

	/**
	 * Marker value for multicolor names.
	 */
	const MULTI = 'multi';

	/**
	 * Normalized name => hex.
	 *
	 * @var array|null
	 */
	private static $map = null;

	/**
	 * Words in an attribute name that mark it as a color attribute.
	 *
	 * @param string $name Attribute slug and label.
	 * @return bool
	 */
	public static function is_color_attribute_name( $name ) {
		$name = self::normalize( $name );
		return (bool) preg_match( '/(colou?r|couleur|farbe|colore|renk|لون|الوان)/u', $name );
	}

	/**
	 * The name to work out a term's color from. When the store Product Translations shows "إيكرو" for "ECRU"
	 * on Arabic pages, the color still comes from the original English name.
	 *
	 * @param WP_Term|object $term Term.
	 * @return string
	 */
	public static function term_name( $term ) {
		if ( ! is_object( $term ) || ! isset( $term->name ) ) {
			return '';
		}
		if ( isset( $term->term_id ) && function_exists( 'mpt_original_term_name' ) ) {
			$original = (string) mpt_original_term_name( (int) $term->term_id );
			if ( '' !== $original ) {
				return $original;
			}
		}
		return (string) $term->name;
	}

	/**
	 * Resolve a name into one or more hex colors.
	 *
	 * @param string $label Term name.
	 * @param bool   $fuzzy Allow matching a color word inside a longer name ("Matte Black").
	 * @return string[] Hex colors, empty when nothing matched.
	 */
	public static function resolve( $label, $fuzzy = true ) {
		$label = trim( wp_strip_all_tags( html_entity_decode( (string) $label, ENT_QUOTES, 'UTF-8' ) ) );
		if ( '' === $label ) {
			return array();
		}

		if ( preg_match( '/#([0-9a-f]{6}|[0-9a-f]{3})\b/i', $label, $m ) ) {
			return array( '#' . strtolower( $m[1] ) );
		}

		$single = self::resolve_single( $label, false );
		if ( $single ) {
			return self::expand( $single );
		}

		// Two-tone names: "Black / White", "Red & Blue", "اسود و ابيض".
		$parts = preg_split( '/\s*(?:\/|\\\\|&|\+|,|،|\||\s-\s|\band\b|\bwith\b|\sو\s)\s*/iu', $label, -1, PREG_SPLIT_NO_EMPTY );
		if ( is_array( $parts ) && count( $parts ) > 1 ) {
			$colors = array();
			foreach ( $parts as $part ) {
				$hex = self::resolve_single( $part, $fuzzy );
				if ( ! $hex || self::MULTI === $hex ) {
					$colors = array();
					break;
				}
				$colors[] = $hex;
			}
			if ( $colors ) {
				return array_slice( $colors, 0, 4 );
			}
		}

		if ( $fuzzy ) {
			$single = self::resolve_single( $label, true );
			if ( $single ) {
				return self::expand( $single );
			}
		}

		return array();
	}

	/**
	 * Is a hex color light enough to need a border?
	 *
	 * @param string $hex Hex color.
	 * @return bool
	 */
	public static function is_light( $hex ) {
		$rgb = self::rgb( $hex );
		if ( ! $rgb ) {
			return false;
		}
		return ( 0.299 * $rgb[0] + 0.587 * $rgb[1] + 0.114 * $rgb[2] ) > 225;
	}

	/**
	 * CSS background for a list of colors.
	 *
	 * @param string[] $colors Hex colors.
	 * @return string
	 */
	public static function background( array $colors ) {
		$colors = array_values( array_filter( array_map( 'sanitize_hex_color', $colors ) ) );
		$count  = count( $colors );

		if ( 0 === $count ) {
			return '';
		}
		if ( 1 === $count ) {
			return $colors[0];
		}
		if ( 2 === $count ) {
			return 'linear-gradient(135deg, ' . $colors[0] . ' 50%, ' . $colors[1] . ' 50%)';
		}

		$step  = 360 / $count;
		$stops = array();
		foreach ( $colors as $i => $hex ) {
			$stops[] = $hex . ' ' . round( $i * $step, 2 ) . 'deg ' . round( ( $i + 1 ) * $step, 2 ) . 'deg';
		}
		return 'conic-gradient(' . implode( ', ', $stops ) . ')';
	}

	/**
	 * Lowercase, unify Arabic letter forms, collapse separators.
	 *
	 * @param string $text Raw text.
	 * @return string
	 */
	public static function normalize( $text ) {
		$text = (string) $text;
		$text = function_exists( 'mb_strtolower' ) ? mb_strtolower( $text, 'UTF-8' ) : strtolower( $text );
		$text = str_replace( array( 'أ', 'إ', 'آ', 'ٱ' ), 'ا', $text );
		$text = str_replace( array( 'ى', 'ة' ), array( 'ي', 'ه' ), $text );
		$text = preg_replace( '/[\x{064B}-\x{0652}\x{0640}]/u', '', $text );
		$text = preg_replace( '/[_\-\.\(\)]+/u', ' ', $text );
		$text = preg_replace( '/\s+/u', ' ', $text );
		return trim( $text );
	}

	/**
	 * Resolve one color phrase.
	 *
	 * @param string $label Phrase.
	 * @param bool   $fuzzy Search for a color word inside the phrase.
	 * @return string Hex, the MULTI marker, or empty.
	 */
	private static function resolve_single( $label, $fuzzy ) {
		$name = self::normalize( $label );
		if ( '' === $name ) {
			return '';
		}

		$hex = self::lookup( $name );
		if ( $hex ) {
			return $hex;
		}

		// "Light Pink", "Dark Green", "ازرق فاتح", "اخضر غامق".
		if ( preg_match( '/^(light|lt|pale|baby|soft|pastel|dark|dk|deep)\s+(.+)$/u', $name, $m )
			|| preg_match( '/^(.+)\s+(فاتح|غامق|داكن)$/u', $name, $m ) ) {
			$is_arabic = in_array( $m[2], array( 'فاتح', 'غامق', 'داكن' ), true );
			$modifier  = $is_arabic ? $m[2] : $m[1];
			$base_name = $is_arabic ? $m[1] : $m[2];
			$base      = self::lookup( $base_name );
			if ( $base && self::MULTI !== $base ) {
				$lighter = in_array( $modifier, array( 'light', 'lt', 'pale', 'baby', 'soft', 'pastel', 'فاتح' ), true );
				return $lighter ? self::mix( $base, '#ffffff', 0.45 ) : self::mix( $base, '#000000', 0.35 );
			}
		}

		if ( ! $fuzzy ) {
			return '';
		}

		// Longest color phrase inside the name, preferring words near the end ("Matte Jet Black").
		$tokens = explode( ' ', $name );
		$total  = count( $tokens );
		for ( $len = min( 3, $total ); $len >= 1; $len-- ) {
			for ( $start = $total - $len; $start >= 0; $start-- ) {
				$hex = self::lookup( implode( ' ', array_slice( $tokens, $start, $len ) ) );
				if ( $hex ) {
					return $hex;
				}
			}
		}

		return '';
	}

	/**
	 * Exact lookup, with and without spaces.
	 *
	 * @param string $name Normalized name.
	 * @return string
	 */
	private static function lookup( $name ) {
		$map = self::map();
		if ( isset( $map[ $name ] ) ) {
			return $map[ $name ];
		}
		$compact = str_replace( ' ', '', $name );
		return isset( $map[ $compact ] ) ? $map[ $compact ] : '';
	}

	/**
	 * Expand the multicolor marker into a rainbow.
	 *
	 * @param string $hex Hex or marker.
	 * @return string[]
	 */
	private static function expand( $hex ) {
		if ( self::MULTI === $hex ) {
			return array( '#e53935', '#fb8c00', '#fdd835', '#43a047', '#1e88e5', '#8e24aa' );
		}
		return array( $hex );
	}

	/**
	 * Mix two hex colors.
	 *
	 * @param string $hex    Base.
	 * @param string $target Mix toward.
	 * @param float  $amount 0..1.
	 * @return string
	 */
	private static function mix( $hex, $target, $amount ) {
		$a = self::rgb( $hex );
		$b = self::rgb( $target );
		if ( ! $a || ! $b ) {
			return $hex;
		}
		$out = '#';
		for ( $i = 0; $i < 3; $i++ ) {
			$out .= str_pad( dechex( (int) round( $a[ $i ] + ( $b[ $i ] - $a[ $i ] ) * $amount ) ), 2, '0', STR_PAD_LEFT );
		}
		return $out;
	}

	/**
	 * Hex to RGB.
	 *
	 * @param string $hex Hex color.
	 * @return int[]|null
	 */
	private static function rgb( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( ! preg_match( '/^[0-9a-f]{6}$/i', $hex ) ) {
			return null;
		}
		return array( hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) );
	}

	/**
	 * The full name map, normalized. Extend with the `kmvs_color_map` filter.
	 *
	 * @return array
	 */
	private static function map() {
		if ( null !== self::$map ) {
			return self::$map;
		}

		$raw = apply_filters( 'kmst_color_map', array_merge( self::css_colors(), self::retail_colors(), self::arabic_colors() ) );

		self::$map = array();
		foreach ( (array) $raw as $name => $hex ) {
			$key = self::normalize( $name );
			if ( '' === $key ) {
				continue;
			}
			$value = self::MULTI === $hex ? self::MULTI : strtolower( (string) $hex );
			self::$map[ $key ] = $value;

			$compact = str_replace( ' ', '', $key );
			if ( ! isset( self::$map[ $compact ] ) ) {
				self::$map[ $compact ] = $value;
			}
		}

		return self::$map;
	}

	/**
	 * CSS named colors.
	 *
	 * @return array
	 */
	private static function css_colors() {
		return array(
			'aliceblue' => '#f0f8ff', 'antiquewhite' => '#faebd7', 'aqua' => '#00ffff', 'aquamarine' => '#7fffd4',
			'azure' => '#f0ffff', 'beige' => '#f5f5dc', 'bisque' => '#ffe4c4', 'black' => '#000000',
			'blanchedalmond' => '#ffebcd', 'blueviolet' => '#8a2be2', 'burlywood' => '#deb887', 'cadetblue' => '#5f9ea0',
			'chartreuse' => '#7fff00', 'chocolate' => '#d2691e', 'coral' => '#ff7f50', 'cornflowerblue' => '#6495ed',
			'cornsilk' => '#fff8dc', 'crimson' => '#dc143c', 'cyan' => '#00ffff', 'darkblue' => '#00008b',
			'darkcyan' => '#008b8b', 'darkgoldenrod' => '#b8860b', 'darkgray' => '#a9a9a9', 'darkgreen' => '#006400',
			'darkgrey' => '#a9a9a9', 'darkkhaki' => '#bdb76b', 'darkmagenta' => '#8b008b', 'darkolivegreen' => '#556b2f',
			'darkorange' => '#ff8c00', 'darkorchid' => '#9932cc', 'darkred' => '#8b0000', 'darksalmon' => '#e9967a',
			'darkseagreen' => '#8fbc8f', 'darkslateblue' => '#483d8b', 'darkslategray' => '#2f4f4f', 'darkturquoise' => '#00ced1',
			'darkviolet' => '#9400d3', 'deeppink' => '#ff1493', 'deepskyblue' => '#00bfff', 'dimgray' => '#696969',
			'dodgerblue' => '#1e90ff', 'firebrick' => '#b22222', 'floralwhite' => '#fffaf0', 'forestgreen' => '#228b22',
			'fuchsia' => '#ff00ff', 'gainsboro' => '#dcdcdc', 'ghostwhite' => '#f8f8ff', 'goldenrod' => '#daa520',
			'greenyellow' => '#adff2f', 'honeydew' => '#f0fff0', 'hotpink' => '#ff69b4', 'indianred' => '#cd5c5c',
			'indigo' => '#4b0082', 'ivory' => '#fffff0', 'lavender' => '#e6e6fa', 'lavenderblush' => '#fff0f5',
			'lawngreen' => '#7cfc00', 'lemonchiffon' => '#fffacd', 'lightblue' => '#add8e6', 'lightcoral' => '#f08080',
			'lightcyan' => '#e0ffff', 'lightgoldenrodyellow' => '#fafad2', 'lightgray' => '#d3d3d3', 'lightgreen' => '#90ee90',
			'lightgrey' => '#d3d3d3', 'lightpink' => '#ffb6c1', 'lightsalmon' => '#ffa07a', 'lightseagreen' => '#20b2aa',
			'lightskyblue' => '#87cefa', 'lightslategray' => '#778899', 'lightsteelblue' => '#b0c4de', 'lightyellow' => '#ffffe0',
			'lime' => '#00ff00', 'limegreen' => '#32cd32', 'linen' => '#faf0e6', 'magenta' => '#ff00ff',
			'maroon' => '#800000', 'mediumaquamarine' => '#66cdaa', 'mediumblue' => '#0000cd', 'mediumorchid' => '#ba55d3',
			'mediumpurple' => '#9370db', 'mediumseagreen' => '#3cb371', 'mediumslateblue' => '#7b68ee', 'mediumspringgreen' => '#00fa9a',
			'mediumturquoise' => '#48d1cc', 'mediumvioletred' => '#c71585', 'midnightblue' => '#191970', 'mintcream' => '#f5fffa',
			'mistyrose' => '#ffe4e1', 'moccasin' => '#ffe4b5', 'navajowhite' => '#ffdead', 'oldlace' => '#fdf5e6',
			'olivedrab' => '#6b8e23', 'orangered' => '#ff4500', 'orchid' => '#da70d6', 'palegoldenrod' => '#eee8aa',
			'palegreen' => '#98fb98', 'paleturquoise' => '#afeeee', 'palevioletred' => '#db7093', 'papayawhip' => '#ffefd5',
			'peachpuff' => '#ffdab9', 'peru' => '#cd853f', 'plum' => '#dda0dd', 'powderblue' => '#b0e0e6',
			'rebeccapurple' => '#663399', 'rosybrown' => '#bc8f8f', 'royalblue' => '#4169e1', 'saddlebrown' => '#8b4513',
			'salmon' => '#fa8072', 'sandybrown' => '#f4a460', 'seagreen' => '#2e8b57', 'seashell' => '#fff5ee',
			'sienna' => '#a0522d', 'silver' => '#c0c0c0', 'skyblue' => '#87ceeb', 'slateblue' => '#6a5acd',
			'slategray' => '#708090', 'snow' => '#fffafa', 'springgreen' => '#00ff7f', 'steelblue' => '#4682b4',
			'tan' => '#d2b48c', 'teal' => '#008080', 'thistle' => '#d8bfd8', 'tomato' => '#ff6347',
			'turquoise' => '#40e0d0', 'violet' => '#ee82ee', 'wheat' => '#f5deb3', 'white' => '#ffffff',
			'whitesmoke' => '#f5f5f5', 'yellowgreen' => '#9acd32',
		);
	}

	/**
	 * Fashion and retail color names. Primaries use store-friendly tones instead of pure CSS values.
	 *
	 * @return array
	 */
	private static function retail_colors() {
		return array(
			'red' => '#e53935', 'blue' => '#1e5bd8', 'green' => '#2e7d32', 'yellow' => '#fdd835',
			'orange' => '#fb8c00', 'purple' => '#7b1fa2', 'pink' => '#f48fb1', 'brown' => '#795548',
			'gray' => '#9e9e9e', 'grey' => '#9e9e9e', 'gold' => '#d4af37', 'golden' => '#d4af37',
			'navy' => '#1f2a44', 'navy blue' => '#1f2a44', 'olive' => '#708238', 'olive green' => '#708238',
			'khaki' => '#c3b091', 'off white' => '#f8f6f0', 'cream' => '#fffdd0', 'ecru' => '#c2b280',
			'nude' => '#e3bc9a', 'skin' => '#f1c27d', 'camel' => '#c19a6b', 'caramel' => '#c68e17',
			'honey' => '#c68e17', 'cognac' => '#9a463d', 'mocha' => '#967969', 'coffee' => '#6f4e37',
			'cafe' => '#6f4e37', 'espresso' => '#4b3621', 'dark brown' => '#4e342e', 'taupe' => '#8b7d6b',
			'stone' => '#928e85', 'sand' => '#c2b280', 'burgundy' => '#800020', 'wine' => '#722f37',
			'bordeaux' => '#5c0120', 'cherry' => '#990f02', 'ruby' => '#9b111e', 'scarlet' => '#ff2400',
			'rust' => '#b7410e', 'brick' => '#cb4154', 'terracotta' => '#e2725b', 'copper' => '#b87333',
			'bronze' => '#cd7f32', 'rose gold' => '#b76e79', 'champagne' => '#f7e7ce', 'mustard' => '#e1ad01',
			'lemon' => '#fff44f', 'peach' => '#ffcba4', 'apricot' => '#fbceb1', 'blush' => '#fe828c',
			'rose' => '#e8a0b0', 'dusty rose' => '#dcae96', 'baby pink' => '#f4c2c2', 'mauve' => '#b784a7',
			'lilac' => '#c8a2c8', 'raspberry' => '#e30b5c', 'berry' => '#990f4b', 'mint' => '#aaf0d1',
			'mint green' => '#aaf0d1', 'sage' => '#9caf88', 'army green' => '#4b5320', 'military' => '#4b5320',
			'emerald' => '#50c878', 'jade' => '#00a86b', 'bottle green' => '#006a4e', 'neon green' => '#39ff14',
			'petrol' => '#005f6a', 'baby blue' => '#89cff0', 'denim' => '#1560bd', 'jeans' => '#5d7fa3',
			'cobalt' => '#0047ab', 'electric blue' => '#7df9ff', 'charcoal' => '#36454f', 'graphite' => '#383838',
			'anthracite' => '#293133', 'ash' => '#b2beb5', 'heather grey' => '#b6b6b4', 'heather gray' => '#b6b6b4',
			'platinum' => '#e5e4e2', 'pearl' => '#eae0c8', 'milky' => '#fdfff5', 'jet black' => '#0a0a0a',
			'cappuccino' => '#a67b5b', 'capuccino' => '#a67b5b', 'cappucino' => '#a67b5b', 'latte' => '#c8a27c',
			'visone' => '#8b7b70', 'mink' => '#8b7b70', 'powder' => '#f1dcd6', 'cipria' => '#e9c9bd',
			'blush pink' => '#fe828c', 'chocolate brown' => '#5d3a1a', 'dusty pink' => '#d8a7a7', 'old rose' => '#c08081',
			'multi' => self::MULTI, 'multicolor' => self::MULTI, 'multicolour' => self::MULTI,
			'multi color' => self::MULTI, 'multi colour' => self::MULTI, 'rainbow' => self::MULTI,
		);
	}

	/**
	 * Arabic color names, including common Egyptian store names.
	 *
	 * @return array
	 */
	private static function arabic_colors() {
		return array(
			'احمر' => '#e53935', 'ازرق' => '#1e5bd8', 'اخضر' => '#2e7d32', 'اصفر' => '#fdd835',
			'اسود' => '#000000', 'ابيض' => '#ffffff', 'رمادي' => '#9e9e9e', 'رصاصي' => '#9e9e9e',
			'سكني' => '#b0b0b0', 'فضي' => '#c0c0c0', 'ذهبي' => '#d4af37', 'برتقالي' => '#fb8c00',
			'بنفسجي' => '#7b1fa2', 'موف' => '#b784a7', 'ليلكي' => '#c8a2c8', 'وردي' => '#f48fb1',
			'بمبي' => '#f48fb1', 'روز' => '#e8a0b0', 'فوشيا' => '#ff00ff', 'فوشي' => '#ff00ff',
			'بني' => '#795548', 'كافيه' => '#6f4e37', 'قهوة' => '#6f4e37', 'قهوه' => '#6f4e37',
			'كحلي' => '#1f2a44', 'نيلي' => '#4b0082', 'لبني' => '#89cff0', 'سماوي' => '#87ceeb',
			'تركواز' => '#40e0d0', 'تركوازي' => '#40e0d0', 'زيتي' => '#708238', 'زيتوني' => '#708238',
			'فستقي' => '#93c572', 'منت' => '#aaf0d1', 'نعناعي' => '#aaf0d1', 'بيج' => '#e8dcc4',
			'كريمي' => '#fffdd0', 'اوف وايت' => '#f8f6f0', 'اوفوايت' => '#f8f6f0', 'عاجي' => '#fffff0',
			'جملي' => '#c19a6b', 'عسلي' => '#c68e17', 'هافان' => '#5b2c2c', 'نبيتي' => '#722f37',
			'خمري' => '#722f37', 'عنابي' => '#800020', 'طوبي' => '#cb4154', 'مستردة' => '#e1ad01',
			'مسطردة' => '#e1ad01', 'خردلي' => '#e1ad01', 'برونزي' => '#cd7f32', 'نحاسي' => '#b87333',
			'بترولي' => '#005f6a', 'شامبين' => '#f7e7ce', 'نود' => '#e3bc9a', 'سلمون' => '#fa8072',
			'سيمون' => '#fa8072', 'خوخي' => '#ffcba4', 'فحمي' => '#36454f', 'ملون' => self::MULTI,
			'متعدد الالوان' => self::MULTI,
		);
	}
}
