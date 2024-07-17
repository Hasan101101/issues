
<?php

/**
 * Plugin Name: Book Management System
 * Description: This plugin is for learning purposes
 * Version:           1.0.0
 * Author:            Hasan
 * Author URI:        https://google.com
 * Text Domain:       book-management-system
 */


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class wedevs_book_management_system {
    public function __construct() {
        add_action('init', array($this, 'init'));
    }

    public function init() {
        add_filter('the_content', array($this, 'show_chapters_in_book'));
        add_filter('the_content', array($this, 'show_book_in_chapter'));
        add_filter('the_content', array($this, 'show_related_books'));
        add_filter('post_type_link', array($this, 'customize_chapter_permalink'), 1, 2);
    }

 
    

    public function show_chapters_in_book($content) {
        if (is_singular('book')) {
            $book_id = get_the_ID();

            $args = array(
                'post_type' => 'chapter',
                'meta_query' => array(
                    'key' => 'select_book',
                    'value' => $book_id,
                    'compare' => '='
                ),
                'meta_key' => 'chapter_number',
                'posts_per_page' => '-1',
                'orderby' => 'meta_value_num',
                'order' => 'ASC'
            );

            $chapters = get_posts($args);

            if ($chapters) {
                $heading = '<h2>Chapters</h2>';
                $content = $content . $heading;
                $content .= '<ul>';
                foreach ($chapters as $chapter) {
                    $content .= '<li><a href="' . get_permalink($chapter->ID) . '">' . $chapter->post_title . '</a></li>';
                }
                $content .= '</ul>';
            }
        }
        return $content;
    }

    public function show_book_in_chapter($content) {
        if (is_singular('chapter')) {
            $chapter_id = get_the_ID();
            $book_id = get_post_meta($chapter_id, 'select_book', true);
            $image = get_the_post_thumbnail($book_id, 'medium');
            $image_html = '<p><a href="' . get_permalink($book_id) . '">' . $image . '</a></p>';
            $heading = '<h3>' . get_the_title($book_id) . '</h3>';
            $content = $heading . $image_html . $content;
        }

        return $content;
    }

    public function show_related_books($content) {
        if (is_singular('book')) {
            $book_id = get_the_ID();
            $genre = get_post_meta($book_id, 'genre', true);

            $args = array(
                'post_type' => 'book',
                'post__not_in' => array($book_id),
                'meta_key' => 'genre',
                'meta_value' => $genre
            );

            $related_books = get_posts($args);
            if ($related_books) {
                $content .= '<h2>Related Books</h2>';
                $content .= '<ul>';

                foreach ($related_books as $related_book) {
                    $content .= '<li><a href="' . get_permalink($related_book->ID) . '">' . $related_book->post_title . '</a></li>';
                }
                $content .= '</ul>';
            }
        }
        return $content;
    }

    public function customize_chapter_permalink($post_link, $post) {
        if (get_post_type($post) == 'chapter') {
            $book_id = get_post_meta($post->ID, 'select_book', true);
            $book = get_post($book_id);
            if ($book) {
                $post_link = str_replace('%bookname%', $book->post_name, $post_link);
            }
        }
        return $post_link;
    }
}
new wedevs_book_management_system;