    // We group by email to avoid duplicates
    $stmt = $pdo->prepare("
        SELECT DISTINCT client_email, child_names
        FROM turns
        WHERE client_email LIKE ?
        ORDER BY start_time DESC
        LIMIT 5
    ");
    $stmt->execute(["%$q%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    log_debug("Clients search results", ['count' => count($results)]);
    echo json_encode($results);
} catch (Exception $e) {
    log_debug("Clients search error: " . $e->getMessage());
    error_log("DB Error: " . $e->getMessage());
    echo json_encode([]);
}
?>
