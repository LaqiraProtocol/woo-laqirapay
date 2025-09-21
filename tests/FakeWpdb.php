<?php
if (!defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
}
if (!class_exists('wpdb')) {
    class wpdb {}
}
class FakeWpdb extends wpdb {
    public $prefix = 'wp_';
    public $insert_id = 0;
    public $rows_affected = 0;
    public array $data = [];

    public function insert($table, $data, $format) {
        $this->insert_id++;
        $data['id'] = $this->insert_id;
        $this->data[] = $data;
    }

    public function get_charset_collate() {
        return '';
    }

    public function prepare($query, ...$args) {
        if (isset($args[0]) && is_array($args[0])) {
            $args = $args[0];
        }
        $query = str_replace(['%f'], ['%F'], $query);
        return vsprintf($query, $args);
    }

    public function get_results($sql, $output = ARRAY_A) {
        if (preg_match('/WHERE id > (\d+) AND created_at < ([0-9-: ]+) ORDER BY id ASC LIMIT (\d+)/', $sql, $m)) {
            $cursor = (int) $m[1];
            $date   = $m[2];
            $limit  = (int) $m[3];
            $rows = array_filter($this->data, fn($row) => $row['id'] > $cursor && $row['created_at'] < $date);
            $rows = array_slice(array_values($rows), 0, $limit);
            return $rows;
        }
        if (preg_match('/WHERE id > (\d+) ORDER BY id ASC LIMIT (\d+)/', $sql, $m)) {
            $cursor = (int) $m[1];
            $limit  = (int) $m[2];
            $rows = array_filter($this->data, fn($row) => $row['id'] > $cursor);
            $rows = array_slice(array_values($rows), 0, $limit);
            return $rows;
        }
        if (preg_match('/ORDER BY created_at DESC LIMIT (\d+) OFFSET (\d+)/', $sql, $m)) {
            $limit = (int) $m[1];
            $offset = (int) $m[2];
            $rows = array_slice(array_reverse($this->data), $offset, $limit);
            return $rows;
        }
        return [];
    }

    public function get_var($sql) {
        return (string) count($this->data);
    }

    public function esc_like($str) {
        return $str;
    }

    public function query($sql) {
        if (preg_match('/created_at < ([0-9-: ]+)/', $sql, $m)) {
            $date = $m[1];
            $before = count($this->data);
            $this->data = array_values(array_filter($this->data, fn($row) => $row['created_at'] >= $date));
            $this->rows_affected = $before - count($this->data);
        }
        return $this->rows_affected;
    }
}
