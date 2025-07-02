<?php

namespace App\Repositories;

use PDO;
use Exception;

abstract class BaseRepository
{
    protected PDO $db;
    protected string $table;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find a record by ID
     * 
     * @param int $id
     * @return mixed|null
     */
    public function findById(int $id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error finding record: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Save a record (insert or update)
     * 
     * @param array $data
     * @return int|false
     */
    public function save(array $data)
    {
        try {
            // Determine if this is an insert or update
            if (isset($data['id']) && $this->findById($data['id'])) {
                // Update existing record
                $updateFields = [];
                $updateValues = [];
                foreach ($data as $key => $value) {
                    if ($key !== 'id') {
                        $updateFields[] = "$key = ?";
                        $updateValues[] = $value;
                    }
                }
                $updateValues[] = $data['id'];

                $stmt = $this->db->prepare("UPDATE {$this->table} SET " . implode(', ', $updateFields) . " WHERE id = ?");
                $stmt->execute($updateValues);
                return $data['id'];
            } else {
                // Insert new record
                $fields = array_keys($data);
                $placeholders = array_fill(0, count($fields), '?');

                $stmt = $this->db->prepare("INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")");
                $stmt->execute(array_values($data));
                return $this->db->lastInsertId();
            }
        } catch (Exception $e) {
            error_log("Error saving record: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a record by ID
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error deleting record: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find records by specific criteria
     * 
     * @param array $criteria
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findBy(array $criteria, int $limit = 100, int $offset = 0): array
    {
        try {
            $whereClause = [];
            $values = [];
            foreach ($criteria as $key => $value) {
                $whereClause[] = "$key = ?";
                $values[] = $value;
            }

            $query = "SELECT * FROM {$this->table}";
            if (!empty($whereClause)) {
                $query .= " WHERE " . implode(' AND ', $whereClause);
            }
            $query .= " LIMIT ? OFFSET ?";
            $values[] = $limit;
            $values[] = $offset;

            $stmt = $this->db->prepare($query);
            $stmt->execute($values);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error finding records: " . $e->getMessage());
            return [];
        }
    }
} 