<?php include 'header.php'; ?>
<div class="hero">
    <h1>Find Your Perfect Space</h1>
    <p>Zero brokers. Direct owner contacts. Premium PG & rental accommodations explicitly tailored for you.</p>
    
    <div class="search-box">
        <form action="search_results.php" method="GET" style="display:flex; gap:1rem; width:100%; flex-wrap:wrap;">
            <input type="text" name="location" placeholder="City or Location..." required style="flex:1; min-width:200px;">
            <input type="number" name="budget" placeholder="Max Budget (₹) (Optional)" style="flex:1; min-width:200px;">
            <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size:1.1rem;"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </form>
    </div>
</div>

<div class="container">
    <h2 style="text-align:center; margin-bottom: 3rem; color: var(--secondary); font-size: 2.5rem; font-weight: 700;">Featured Properties</h2>
    <div class="grid">
        <?php
        require_once 'db.php';
        $sql = "SELECT p.*, u.Name as OwnerName FROM Properties p JOIN Users u ON p.OwnerID = u.UserID WHERE p.Status = 'Active' ORDER BY p.CreatedAt DESC LIMIT 6";
        $result = $conn->query($sql);
        
        $images = [
            'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&q=80', 
            'https://images.unsplash.com/photo-1502672260266-1c1c294812b1?w=800&q=80', 
            'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&q=80', 
            'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&q=80',
            'https://images.unsplash.com/photo-1628611225249-6c3c7c689552?w=800&q=80',
            'https://images.unsplash.com/photo-1505691938895-1758d7bef511?w=800&q=80'
        ];
        
        if($result && $result->num_rows > 0) {
            $imgIndex = 0;
            while($row = $result->fetch_assoc()) {
                $img = $images[$imgIndex % count($images)];
                $imgIndex++;
                
                echo '<div class="card">';
                echo '<div class="card-img" style="background-image: url(\''.$img.'\');">';
                echo '<div class="card-badge">Featured</div>';
                echo '</div>';
                echo '<div class="card-content">';
                echo '<h3 class="card-title">Modern PG in ' . htmlspecialchars($row['Location']) . '</h3>';
                echo '<div class="card-price">₹' . number_format($row['Price']) . '<span style="font-size:1rem;font-weight:400;color:var(--text-muted)">/mo</span></div>';
                echo '<div class="card-location"><i class="fa-solid fa-location-dot" style="color:var(--primary)"></i> ' . htmlspecialchars($row['Location']) . '</div>';
                
                // Truncate facilities
                $facs = htmlspecialchars($row['Facilities']);
                if(strlen($facs) > 50) $facs = substr($facs, 0, 50) . '...';
                echo '<p style="color:var(--text-muted); font-size:0.9rem; margin-bottom: 1.5rem; flex-grow:1;"><i class="fa-solid fa-check-circle"></i> ' . $facs . '</p>';
                
                echo '<a href="property_details.php?id=' . $row['PropertyID'] . '" class="btn btn-primary" style="width:100%; text-align:center;">View Details <i class="fa-solid fa-arrow-right" style="margin-left:5px;"></i></a>';
                echo '</div></div>';
            }
        } else {
            echo '<div style="text-align:center; grid-column: 1/-1; padding: 4rem; background: var(--white); border-radius: 20px; box-shadow: var(--shadow); border: 1px solid var(--border);">';
            echo '<i class="fa-solid fa-house-circle-exclamation" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>';
            echo '<h3 style="font-size: 1.5rem; color: var(--secondary); margin-bottom: 1rem;">No active properties found right now.</h3>';
            echo '<p style="color: var(--text-muted);">Owners are adding new properties frequently. Please check back later!</p>';
            echo '</div>';
        }
        ?>
    </div>
</div>

<div style="background: var(--white); padding: 5rem 5%; margin-top: 4rem; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="color: var(--secondary); font-size: 2.5rem; margin-bottom: 3rem;">Why Choose Stayly?</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 3rem;">
            <div>
                <i class="fa-solid fa-hand-holding-dollar" style="font-size: 3rem; color: var(--primary); margin-bottom: 1.5rem;"></i>
                <h3 style="color: var(--secondary); margin-bottom: 1rem; font-size: 1.35rem;">Zero Brokerage</h3>
                <p style="color: var(--text-muted);">Connect directly with owners and save thousands on brokerage fees.</p>
            </div>
            <div>
                <i class="fa-solid fa-shield-halved" style="font-size: 3rem; color: var(--primary); margin-bottom: 1.5rem;"></i>
                <h3 style="color: var(--secondary); margin-bottom: 1rem; font-size: 1.35rem;">Verified Listings</h3>
                <p style="color: var(--text-muted);">All properties and owners undergo a strict verification process for your safety.</p>
            </div>
            <div>
                <i class="fa-solid fa-bolt" style="font-size: 3rem; color: var(--primary); margin-bottom: 1.5rem;"></i>
                <h3 style="color: var(--secondary); margin-bottom: 1rem; font-size: 1.35rem;">Instant Updates</h3>
                <p style="color: var(--text-muted);">Get real-time notifications for new properties and price drops.</p>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
