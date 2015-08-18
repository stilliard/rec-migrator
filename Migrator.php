<?php

/**
 * Migrator
 *
 * Migrting from old "REC Designer" template language to Twig
 *
 */
class Migrator
{
    /**
     * Whitelist of REC tag namespaces used. (Used to match (\w+), but this conflicts with minified CSS and JS)
     * @var string
     */
    public static $nsWhitelist = 'theme|page|style|colour|product|category|blog|blog_cat|setup|invoice|line|user|video|search|department|blog_content|manufacturer|footer|header|company|date|cart|order|total|link|text';


    /**
     * Convert
     *
     * @param string $data
     * @param bool $addedJSTemplateChanges optional Changes JS code for handling JS templates in templates so they dont conflict with twig (e.g. handlebars, or string.replace() statements)
     */
    public static function convert($data, $addedJSTemplateChanges=false)
    {
        $replacements = array();

        // First convert any theme variable names
        $replacements['{setup:(side_bar_position|website_font|menu_float|header_height)}'] = '{theme:config:$1}';
        $replacements['{style:(footer_background_image|footer_background_image_repeat|main_background_image|main_background_image_repeat|header_background_image|header_background_image_repeat|side_bar_position|use_borders)}'] = '{theme:config:$1}';
        $replacements['{header:(flash_file|flash_height|show_search|show_top_links|show_add_this|show_phone_number|show_logo|show_company_name|show_company_slogan|use_flash_header)}'] = '{theme:config:$1}';
        $replacements['{colour:(\w+)}'] = '{theme:colour:$1}';

        // Fix comments
        $replacements['{\*((\n|\r|.)*?)\*}'] = '{#$1#}';

        // Fix includes
        $replacements['{inc:"(.*?)"}'] = '{% include "css/lib/$1" %}';

        // fix a couple old cases where we used to use css includes for this too
        $replacements['@import url\("reset\.css"\);'] = '{% include "css/lib/reset.css" %}';
        $replacements['@import url\("960_24_col\.css"\);'] = '{% include "css/lib/960_24_col.css" %}';

        // Fix if and elseif statments
        $replacements['{if:(else)?:?"(.*?)"}'] = '{% $1if $2 %}';

        // Fix syntax inside if statments
        $replacements['{% (else)?if .*? %}'] = array(

            // then fix "not" clauses
            ' ?!(?!\=) ?' => ' not ', // match "!" or " ! " but not "!="
            ' ?!= ?' => ' != ', // add spaces (easier to read)
            ' ?&& ?' => ' and ',
            ' ?\|\| ?' => ' or ',

            // fix variables inside statments
            '\'?{(\w+)}\'?' => '$1', // e.g. {a}, {a:b}, {a:b:c}, '{a}' ...
            '\'?{('.self::$nsWhitelist.'):(\w+)}\'?' => '$1.$2',
            '\'?{('.self::$nsWhitelist.'):(\w+):(\w+)}\'?' => '$1.$2.$3',

            // fix any extra spacing inside statments too
            '\s{2,}' => ' ',
        );

        // fix if:else to else
        $replacements['{if:else}'] = '{% else %}';

        // fix if:ends to endif
        $replacements['{if:end}'] = '{% endif %}';

        // last step, Fix tags {a} or { a } or {a:b} or {a:b:c}
        // Negative lookbehind in here "(?<!{)" to make sure we dont pick up existing twig tags!
        // only needed on the first as the 2nd and 3rd also require a colon ":" while twig uses a period "."
        $replacements['(?<!{){ ?(\w+) ?}'] = '{{ $1 }}';
        $replacements['{ ?('.self::$nsWhitelist.'):(\w+) ?}'] = '{{ $1.$2 }}';
        $replacements['{ ?('.self::$nsWhitelist.'):(\w+):(\w+) ?}'] = '{{ $1.$2.$3 }}';

        // Special case, JS return/break/continue statements, just fix them after they have been converted
        // these are reserved JS keywords that could be used ina  single code block and be valid JS
        // luckily we don't use any of these in the legacy template keys
        $replacements['{{ return }}'] = '{return}';
        $replacements['{{ break }}'] = '{break}';
        $replacements['{{ continue }}'] = '{continue}';

        // optional extra replacements used in template files to replace JS templates code that would conflict with Twig
        if ($addedJSTemplateChanges) {
            // Wrap <script> template blocks in {% raw %} tags so the parser wont later touch them
            // EXCEPT the "back in stock" popup on the product_info.html
            $replacements['(<script(?!( id="rec-product-back-in-stock-container")).*?type="text\/(template|x\-[A-z\-]+)".*?>(.|\n)*?<\/script>)'] = '{% raw %}$1{% endraw %}';

            // Fix JS simple replace template key statements
            // e.g. string = string.replace('{{ name }}', user.name);
            $replacements['\.replace\((["\'\/]){{(.*?)}}\1'] = '.replace($1{{ \'{{$2}}\' }}$1';
        }

        // Handle recursively replacing the tags
        $handleReplacements = function ($data, $replacements) use (&$handleReplacements) {

            // loop given replacements,
            // if its an array, handle replacements within the matched context of the key
            foreach ($replacements as $pattern => $replacement) {
                $pattern = '/' . $pattern .'/mi';
                $data = is_array($replacement)
                    ? preg_replace_callback($pattern, function ($matches) use (&$handleReplacements, $replacement) {
                        return $handleReplacements($matches[0], $replacement);
                    }, $data)
                    : preg_replace($pattern, $replacement, $data);
            }

            return $data;
        };

        return $handleReplacements($data, $replacements);
    }
}
