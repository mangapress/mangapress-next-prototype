<?php
/**
 * Fields functions
 * Helper functions for generating options fields markup
 *
 * @package MangaPress_NEXT\Fields
 */
namespace MangaPress\Fields;


/**
 * Output checkbox field
 * @param array $params Field parameters array
 */
function checkbox_field_cb($params)
{
    $option_group = \MangaPress_Options::OPTIONS_GROUP_NAME;
    $description = output_description($params, 'span');

    $field = "<input type='checkbox' id='{$params['id']}' name='{$option_group}[{$params['section']}][{$params['name']}]' />";
    echo $field . $description;
}


/**
 * Output text field
 * @param array $params Field parameters array
 * @param string $class CSS class for styling purposes.
 */
function text_field_cb($params, $class = 'regular-text')
{
    $option_group = \MangaPress_Options::OPTIONS_GROUP_NAME;
    $description = output_description($params);

    $field = "<input class='{$class}' type='text' id='{$params['id']}' name='{$option_group}[{$params['section']}][{$params['name']}]' />";
    echo $field . $description;
}

/**
 * Output text field
 * @param array $params Field parameters array
 */
function select_field_cb($params)
{
    $option_group = \MangaPress_Options::OPTIONS_GROUP_NAME;
    $description = output_description($params);

    $select = "<select id='{$params['id']}' name='{$option_group}[{$params['section']}][{$params['name']}]'>\r\n";
    foreach ($params['value'] as $value => $label) {
        $select .= "\t<option value='{$value}' " . selected($params['default'], $value, false) . ">{$label}</option>\r\n";
    }
    $select .= "</select>\r\n";

    echo $select . $description;
}


/**
 * Output field description
 *
 * @param array $params Field parameters
 *
 * @return string
 */
function output_description($params, $tag = 'p')
{
    if (!isset($params['description'])) {
        return '';
    }

    return "<{$tag} class='description'><label for='{$params['id']}'>{$params['description']}</label></{$tag}>";
}