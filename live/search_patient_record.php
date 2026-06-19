<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Record Search</title>
</head>
<body>
    <h2>Search Patient Records</h2>
    <input type="text" id="searchInput" placeholder="Enter patient name or ID...">
    <button id="searchBtn">Search</button>

    <div id="resultsTable">
        </div>

<script>
// Search logic (same as before)
document.getElementById('searchBtn').addEventListener('click', () => {
    const query = document.getElementById('searchInput').value;
    fetch(`process_search_patient_record.php?q=${encodeURIComponent(query)}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('resultsTable').innerHTML = data;
        });
});

// Click logic for "View Details"
document.getElementById('resultsTable').addEventListener('click', (e) => {
    if (e.target.classList.contains('view-details')) {
        const patientId = e.target.getAttribute('data-id');
        showModal(patientId);
    }
});

function showModal(id) {
    const modal = document.getElementById('detailsModal');
    const content = document.getElementById('modalContent');
    
    modal.style.display = 'block';
    content.innerHTML = "Fetching record...";

    // Fetch specific detail from a new PHP endpoint
    fetch(`get_patient_detail.php?id=${id}`)
        .then(response => response.text())
        .then(data => {
            content.innerHTML = data;
        });
}

// Wrap everything in a 'DOMContentLoaded' to ensure IDs exist
document.addEventListener('DOMContentLoaded', function() {
    
    const modal = document.getElementById('detailsModal');
    const closeBtn = document.getElementById('closeModal');

    // 1. Click the button to close
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // 2. Click outside the white box to close
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // 3. (Optional) Press 'Escape' key to close
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            modal.style.display = 'none';
        }
    });
});
</script>

<div id="detailsModal" style="display:none; position:fixed; z-index:100; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5);">
    <div style="background-color:white; margin:10% auto; padding:20px; width:60%; border-radius:8px; position:relative;">
        
        <span id="closeModal" style="position:absolute; right:20px; top:10px; cursor:pointer; font-size:28px; font-weight:bold; color:#aaa;">&times;</span>
        
        <h3>Visit Details</h3>
        <hr>
        <div id="modalContent"></div>
    </div>
</div>
</body>
</html>