<?php include 'header.php'; ?>
<?php require_once 'db.php'; ?>

<div style="background: var(--secondary); padding: 3rem 5%; text-align: center; color: var(--white); margin-bottom: 3rem;">
    <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">Search Results</h1>
    <form action="" method="GET" style="max-width: 800px; margin: 0 auto; display: flex; gap: 1rem; background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 12px; backdrop-filter: blur(10px);">
        <input type="text" name="location" value="<?php echo isset($_GET['location']) ? htmlspecialchars($_GET['location']) : ''; ?>" placeholder="City or Location..." style="flex:1; padding: 1rem; border:none; border-radius:8px; font-size:1.05rem;" required>
        <input type="number" name="budget" value="<?php echo isset($_GET['budget']) ? htmlspecialchars($_GET['budget']) : ''; ?>" placeholder="Max Budget" style="flex:1; padding: 1rem; border:none; border-radius:8px; font-size:1.05rem;">
        <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size:1.05rem;"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
    </form>
</div>

<div class="container">
    <div class="grid">
        <?php
        $where = "p.Status = 'Active'";
        if(isset($_GET['location']) && !empty($_GET['location'])) {
            $loc = sanitize($conn, $_GET['location']);
            $where .= " AND p.Location LIKE '%$loc%'";
        }
        if(isset($_GET['budget']) && !empty($_GET['budget'])) {
            $budget = floatval($_GET['budget']);
            $where .= " AND p.Price <= $budget";
        }
        
        $sql = "SELECT p.*, u.Name as OwnerName FROM Properties p JOIN Users u ON p.OwnerID = u.UserID WHERE $where ORDER BY p.CreatedAt DESC";
        $result = $conn->query($sql);
        
        $images = [
            'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&q=80', 
            'https://images.unsplash.com/photo-1502672260266-1c1c294812b1?w=800&q=80', 
            'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&q=80',
            'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&q=80'
        ];
        
        if($result && $result->num_rows > 0) {
            $imgIndex = 0;
            while($row = $result->fetch_assoc()) {
                $img = $images[$imgIndex % count($images)];
                $imgIndex++;
                
                echo '<div class="card">';
                echo '<div class="card-img" style="background-image: url(\''.$img.'\');"></div>';
                echo '<div class="card-content">';
                echo '<h3 class="card-title" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">PG in ' . htmlspecialchars($row['Location']) . '</h3>';
                echo '<div class="card-price">₹' . number_format($row['Price']) . '<span style="font-size:1rem;font-weight:400;color:var(--text-muted)">/mo</span></div>';
                echo '<div class="card-location"><i class="fa-solid fa-location-dot" style="color:var(--primary)"></i> ' . htmlspecialchars($row['Location']) . '</div>';
                
                $facs = htmlspecialchars($row['Facilities']);
                if(strlen($facs) > 40) $facs = substr($facs, 0, 40) . '...';
                echo '<p style="color:var(--text-muted); font-size:0.9rem; margin-bottom: 1.5rem; flex-grow:1;"><i class="fa-solid fa-check"></i> ' . $facs . '</p>';
                
                echo '<a href="property_details.php?id=' . $row['PropertyID'] . '" class="btn btn-primary" style="width:100%; text-align:center;">View Details <i class="fa-solid fa-arrow-right" style="margin-left:5px;"></i></a>';
                echo '</div></div>';
            }
        } else {
            echo '<div style="text-align:center; grid-column: 1/-1; padding: 4rem; background: var(--white); border-radius: 20px; box-shadow: var(--shadow); border: 1px solid var(--border);">';
            echo '<i class="fa-regular fa-face-frown" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>';
            echo '<h3 style="font-size: 1.5rem; color: var(--secondary); margin-bottom: 1rem;">No properties matched your search.</h3>';
            echo '<p style="color: var(--text-muted);">Try changing your location or increasing your budget.</p>';
            echo '</div>';
        }
        ?>
    </div>
</div>

<?php include 'footer.php'; ?>
