<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'AR Experience | ' . APP_NAME;

$posters = [];
try {
    $posters = requireDatabase()->query('SELECT * FROM ar_posters WHERE status = "active" ORDER BY created_at DESC')->fetchAll();
} catch (Throwable $e) {
    $posters = [];
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="ar-shell">
    <div class="ar-header py-3 px-3 px-md-4">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a href="<?= appUrl('index.php') ?>" class="btn btn-link text-white p-0"><i class="fa-solid fa-arrow-left me-2"></i>Back</a>
            <div class="text-center">
                <div class="fw-bold">AR TOURISM</div>
            </div>
            <div class="text-light small"><?= e($posters ? 'Poster ready' : 'AR not configured') ?></div>
        </div>
    </div>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-10">
                <div class="card border-0 overflow-hidden">
                    <div class="p-3 border-bottom">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-9">
                                <label class="form-label mb-1">Select poster</label>
                                <select id="posterSelect" class="form-select">
                                    <?php foreach ($posters as $poster): ?>
                                        <option value="<?= (int) $poster['id'] ?>"><?= e($poster['name']) ?> (<?= e($poster['target_status']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 d-grid">
                                <button id="startAr" type="button" class="btn btn-primary">Start AR</button>
                            </div>
                        </div>
                    </div>
                    <div class="ar-camera-wrap position-relative">
                        <div id="ar-view"></div>
                        <div class="ar-overlay" id="arMessage">
                            <div>
                                <h2 class="display-6 mb-2">Welcome to AR Tourism</h2>
                                <p class="mb-0">Allow camera access to begin the AR experience.</p>
                            </div>
                        </div>
                        <div class="ar-status" id="arStatus">Waiting for camera permission</div>
                        <div class="ar-panel" id="arPanel"></div>
                        <div class="ar-actions">
                            <button type="button" class="ar-button" aria-label="Mute audio"><i class="fa-solid fa-volume-xmark"></i></button>
                            <button type="button" class="ar-button" aria-label="AR info"><i class="fa-solid fa-circle-info"></i></button>
                            <button type="button" class="ar-button" aria-label="Open YouTube"><i class="fa-brands fa-youtube"></i></button>
                            <button type="button" class="ar-button" aria-label="Open map"><i class="fa-solid fa-map-location-dot"></i></button>
                            <button type="button" class="ar-button" aria-label="Close panel"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/mind-ar@1.2.1/dist/mindar-image.prod.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.158.0/build/three.min.js"></script>
<script>
  const isHttps = location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
  const posterSelect = document.getElementById('posterSelect');
  const startArBtn = document.getElementById('startAr');
  const arStatus = document.getElementById('arStatus');
  const arMessage = document.getElementById('arMessage');
  const arPanel = document.getElementById('arPanel');
  const arView = document.getElementById('ar-view');

  const showStatus = (message) => {
    arStatus.textContent = message;
  };

  const showMessage = (title, body) => {
    arMessage.innerHTML = '<div><h2 class="display-6 mb-2">' + title + '</h2><p class="mb-0">' + body + '</p></div>';
  };

  if (!isHttps) {
    showStatus('Camera access requires HTTPS');
    showMessage('Camera access requires HTTPS', 'Please access this website using an HTTPS URL or localhost for local testing.');
  }

  const showPanel = (hotspot) => {
    if (!hotspot) {
      arPanel.classList.remove('show');
      return;
    }

    arPanel.classList.add('show');
    const imageUrl = hotspot.attraction_image ? hotspot.attraction_image : '<?= appUrl('assets/images/placeholder-landmark.svg') ?>';
    arPanel.innerHTML = `
      <div class="d-flex flex-column gap-3">
        <img src="${imageUrl}" class="img-fluid rounded-3" alt="${hotspot.attraction_name}" style="height: 160px; object-fit: cover;">
        <div>
          <h3 class="h5 mb-1">${hotspot.attraction_name}</h3>
          <p class="small text-light mb-0">${hotspot.attraction_short_description || 'Explore this tourism attraction.'}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
          ${hotspot.video_url ? '<a href="' + hotspot.video_url + '" class="btn btn-sm btn-primary" target="_blank" rel="noreferrer">Watch video</a>' : ''}
          ${hotspot.google_maps_url ? '<a href="' + hotspot.google_maps_url + '" class="btn btn-sm btn-outline-light" target="_blank" rel="noreferrer">Map</a>' : ''}
          <button type="button" class="btn btn-sm btn-outline-light" data-close-panel>Close</button>
        </div>
      </div>
    `;

    arPanel.querySelector('[data-close-panel]').addEventListener('click', () => arPanel.classList.remove('show'));
  };

  startArBtn.addEventListener('click', async () => {
    if (!isHttps) {
      return;
    }

    const posterId = posterSelect.value;
    if (!posterId) {
      showStatus('No poster available');
      return;
    }

    showStatus('Loading AR target');
    arMessage.innerHTML = '<div><h2 class="display-6 mb-2">Preparing AR Target</h2><p class="mb-0">Please wait...</p></div>';

    try {
      const response = await fetch('<?= appUrl('api/ar/poster.php') ?>?poster_id=' + encodeURIComponent(posterId));
      const data = await response.json();

      if (!data.success || !data.poster) {
        showStatus('AR target is not ready');
        showMessage('AR tracking is not ready', 'Please ask the administrator to compile this poster and configure hotspots.');
        return;
      }

      if (!data.poster.target_file) {
        showStatus('Poster target is not compiled');
        showMessage('AR target missing', 'The poster has not yet been compiled for tracking. Please ask the administrator to compile the target.');
        return;
      }

      showStatus('Poster detected');
      arMessage.innerHTML = '<div><h2 class="display-6 mb-2">Poster detected</h2><p class="mb-0">Explore the attractions</p></div>';

      const mindarThree = new window.MindARThree({
        container: arView,
        imageTargetSrc: '<?= appUrl('assets/ar-targets/') ?>' + data.poster.target_file,
        maxTrack: 1,
        uiScanning: false,
        uiLoading: 'no'
      });

      const { renderer, scene, camera, anchor, onUpdate } = mindarThree;
      const light = new THREE.AmbientLight(0xffffff, 1);
      scene.add(light);

      const hotspots = data.hotspots || [];
      hotspots.forEach((hotspot) => {
        const marker = document.createElement('button');
        marker.className = 'hotspot';
        marker.textContent = hotspot.label ? hotspot.label.slice(0, 1).toUpperCase() : 'A';
        marker.title = hotspot.attraction_name || 'Attraction';
        marker.style.left = (hotspot.x * 100) + '%';
        marker.style.top = (hotspot.y * 100) + '%';
        marker.style.width = (hotspot.width * 300) + 'px';
        marker.style.height = (hotspot.height * 220) + 'px';
        marker.addEventListener('click', () => showPanel(hotspot));
        arView.appendChild(marker);
      });

      const start = async () => {
        await mindarThree.start();
        arView.appendChild(renderer.domElement);
        const anchorGroup = new THREE.Group();
        anchor.group.add(anchorGroup);
        renderer.setAnimationLoop(() => {
          renderer.render(scene, camera);
        });
      };

      start();
    } catch (error) {
      console.error(error);
      showStatus('AR experience could not start');
      showMessage('AR engine unavailable', 'This browser may not support the required camera features. Please check HTTPS and camera permissions.');
    }
  });
</script>
