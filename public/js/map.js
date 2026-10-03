document.addEventListener("DOMContentLoaded", () => {
  const mapElement = document.getElementById("map");
  if (!mapElement) {
    return;
  }

  // Türkiye'nin merkez koordinatları
  const turkeyCenter = [39.0, 35.0];
  const defaultZoom = 6;

  // Haritayı başlat
  const map = L.map(mapElement).setView(turkeyCenter, defaultZoom);

  // OpenStreetMap katmanını ekle
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 18,
    attribution: "© OpenStreetMap katkıda bulunanlar",
  }).addTo(map);

  const apiBase = window.API_BASE || "../api";
  const currentUserFirstName = window.__CURRENT_USER_FIRST_NAME__ || "";
  const urlParams = new URLSearchParams(window.location.search);
  const initialLocationId = Number(urlParams.get("location"));
  let highlightedLocationId =
    Number.isInteger(initialLocationId) && initialLocationId > 0
      ? initialLocationId
      : null;
  let allLocations = [];
  let activeCategory = "";
  let searchQuery = "";
  let activeCityId = null;
  let activeCityName =
    document.getElementById("map-heading")?.textContent.replace(" Keşfi", "") ||
    "";
  let markers = [];
  let selectedLocations = [];
  let markerMap = new Map(); // location.id -> marker
  let searchTimeout = null;

  function queueSearch(value) {
    searchQuery = value.trim();
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(loadLocations, 250);
  }

  const citySelect = document.getElementById("city-select");
  const headingEl = document.getElementById("map-heading");

  function setHeadingForCity(cityName) {
    const headingText = cityName ? `${cityName} Keşfi` : "Tüm Şehirler Keşfi";
    if (headingEl) {
      headingEl.textContent = headingText;
      headingEl.title = headingText;
    }
  }

  function clearMarkers() {
    markers.forEach((marker) => map.removeLayer(marker));
    markers = [];
  }

  function renderList(locations) {
    const list = document.getElementById("map-location-list");
    if (!list) {
      return;
    }
    if (locations.length === 0) {
      list.innerHTML =
        '<div class="bg-surface-container-lowest rounded-xl p-4 text-on-surface-variant">Sonuç bulunamadı.</div>';
      return;
    }
    list.innerHTML = locations
      .map((location) => {
        const isSelected = selectedLocations.some(
          (s) => Number(s.id) === Number(location.id),
        );
        const name = (location.name || "").toString();
        const category = (location.category || "Diğer").toString();
        const cityName = (location.city_name || "Bilinmiyor").toString();
        const rating = location.avg_rating ?? "0";
        const description = (location.description || "").toString();
        const shortDescription =
          description.length > 120
            ? `${description.slice(0, 120)}...`
            : description;
        const rawImageUrl = (location.image_url || "").toString().trim();
        const rawImage = (location.image || "").toString().trim();
        const imageUrl = rawImageUrl
          ? rawImageUrl
          : rawImage
            ? rawImage.startsWith("http")
              ? rawImage
              : `uploads/${rawImage}`
            : "";
        const imageMarkup = imageUrl
          ? `<img src="${imageUrl}" alt="${name}" loading="lazy" />`
          : `
                    <div class="map-card-placeholder flex items-center justify-center text-primary h-full">
                        <span class="material-symbols-outlined" aria-hidden="true">landscape</span>
                    </div>
                `;

        return `
                <div class="bg-surface-container-lowest rounded-2xl shadow-[0_4px_20px_rgba(0,105,114,0.08)] overflow-hidden group cursor-pointer transition-transform hover:-translate-y-1${isSelected ? " selected border-2 border-primary" : ""}" data-location-id="${location.id}">
                    <div class="relative">
                        <div class="map-card-image">${imageMarkup}</div>
                        <span class="absolute top-3 right-3 bg-secondary-container text-secondary px-2 py-1 rounded-full text-xs font-semibold tracking-wide uppercase">${category}</span>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start gap-3 mb-2">
                            <div>
                                <h3 class="font-headline-md text-[20px] leading-tight font-semibold text-on-surface">
                                    <a class="hover:underline" href="location-detail.php?id=${location.id}">${name}</a>
                                </h3>
                                <p class="font-label-md text-label-md text-on-surface-variant mt-1">${cityName}</p>
                            </div>
                            <div class="flex items-center gap-1 text-secondary bg-secondary-container/30 px-2 py-1 rounded-full">
                                <span class="material-symbols-outlined text-sm" data-weight="fill">star</span>
                                <span class="font-label-md text-label-md">${rating}</span>
                            </div>
                        </div>
                        ${shortDescription ? `<p class="text-sm text-on-surface-variant line-clamp-2">${shortDescription}</p>` : ""}
                    </div>
                </div>
            `;
      })
      .join("");
  }

  function normalizeSearchString(value) {
    return value
      .toString()
      .normalize("NFC")
      .toLocaleLowerCase("tr")
      .replace(/ı/g, "i")
      .replace(/İ/g, "i")
      .replace(/ı/g, "i");
  }

  function applyFilters() {
    const filtered = allLocations.filter((location) => {
      const name = normalizeSearchString(location.name || "");
      const city = normalizeSearchString(location.city_name || "");
      const category = normalizeSearchString(location.category || "");
      const description = normalizeSearchString(location.description || "");
      const query = normalizeSearchString(searchQuery);
      const matchesCategory =
        activeCategory === "" ||
        category === normalizeSearchString(activeCategory);
      const matchesSearch =
        query === "" ||
        name.includes(query) ||
        city.includes(query) ||
        category.includes(query) ||
        description.includes(query);
      return matchesCategory && matchesSearch;
    });

    clearMarkers();
    filtered.forEach((location) => {
      if (!location.lat || !location.lng) {
        return;
      }

      const marker = L.marker([location.lat, location.lng]).addTo(map);
      const popupHtml = `
                <strong>${location.name}</strong><br>
                ${location.city_name || "Bilinmiyor"}
            `;
      marker.bindPopup(popupHtml);
      marker.on("click", () => highlightLocationCard(location.id));
      markers.push(marker);
      markerMap.set(location.id, marker);
    });

    // Ensure map viewport shows the currently rendered markers (only the sidebar items)
    try {
      const visibleMarkers = markers.slice();
      if (visibleMarkers.length === 1) {
        const latlng = visibleMarkers[0].getLatLng();
        // single marker: moderate zoom so context stays visible
        map.setView(latlng, 7);
      } else if (visibleMarkers.length > 1) {
        const bounds = L.latLngBounds(visibleMarkers.map((m) => m.getLatLng()));
        // pad and prevent over-zooming when fitting multiple markers
        map.fitBounds(bounds.pad(0.12), { padding: [80, 80], maxZoom: 7 });
      } else {
        // no markers: reset to country view
        map.setView(turkeyCenter, defaultZoom);
      }
    } catch (e) {
      // ignore fit errors
    }

    renderList(filtered);
    refreshSelectionBadges();
  }
  async function loadLocations() {
    try {
      let url = `${apiBase}/locations/index.php?limit=200`;
      if (activeCityId) {
        url += `&city_id=${activeCityId}`;
      }
      if (activeCategory) {
        url += `&category=${encodeURIComponent(activeCategory)}`;
      }
      if (searchQuery) {
        url += `&q=${encodeURIComponent(searchQuery)}`;
      }

      const response = await fetch(url);
      const payload = await response.json();

      allLocations = Array.isArray(payload.data) ? payload.data : [];
      // reset marker map for fresh render
      markerMap = new Map();
      applyFilters();
      if (highlightedLocationId) {
        highlightLocationCard(highlightedLocationId);
        highlightedLocationId = null;
      }
    } catch (error) {
      console.error("Harita verisi alınamadı:", error);
      const list = document.getElementById("map-location-list");
      if (list) {
        list.innerHTML =
          '<div class="bg-surface-container-lowest rounded-xl p-4 text-error">Veri alınamadı. API bağlantısını kontrol et.</div>';
      }
    }
  }

  async function fetchCitiesAndInit() {
    try {
      const response = await fetch(`${apiBase}/cities/index.php?limit=200`);
      const payload = await response.json();
      const cities = Array.isArray(payload.data) ? payload.data : [];

      if (citySelect && cities.length) {
        // populate
        cities.forEach((c) => {
          const opt = document.createElement("option");
          opt.value = c.id;
          opt.textContent = c.name;
          citySelect.appendChild(opt);
        });

        // try to select initial city by name if present
        if (activeCityName) {
          const match = cities.find(
            (c) => c.name.toLowerCase() === activeCityName.toLowerCase(),
          );
          if (match) {
            citySelect.value = match.id;
            activeCityId = match.id;
          }
        }
      }

      // initial load
      loadLocations();
    } catch (e) {
      console.warn("Şehirler alınamadı", e);
      loadLocations();
    }
  }

  fetchCitiesAndInit();

  const searchInput = document.getElementById("map-search");
  searchInput?.addEventListener("input", (event) => {
    queueSearch(event.target.value);
  });

  document.querySelectorAll("#map-filters button").forEach((button) => {
    button.addEventListener("click", () => {
      document.querySelectorAll("#map-filters button").forEach((btn) => {
        btn.classList.remove("bg-primary", "text-on-primary");
        btn.classList.add(
          "bg-surface-container-high",
          "text-on-surface-variant",
        );
      });
      button.classList.add("bg-primary", "text-on-primary");
      button.classList.remove(
        "bg-surface-container-high",
        "text-on-surface-variant",
      );

      activeCategory = button.dataset.category || "";
      // reload from server with city + category
      loadLocations();
    });
  });

  // City selector
  citySelect?.addEventListener("change", (e) => {
    const val = e.target.value;
    activeCityId = val ? Number(val) : null;
    activeCityName = e.target.selectedOptions?.[0]?.text || "";
    setHeadingForCity(activeCityName);
    // reload list for selected city
    loadLocations();
  });

  // -- Route builder UI --
  const routeCountEl = document.getElementById("route-count");
  const routeClearBtn = document.getElementById("route-clear");
  const createRouteButton = document.getElementById("create-route-button");
  const routeModal = document.getElementById("route-modal");
  const routeCreateForm = document.getElementById("route-create-form");
  const routeCreateName = document.getElementById("route-create-name");
  const routeCreatePublic = document.getElementById("route-create-public");
  const routeCreateError = document.getElementById("route-create-error");
  const routeCreateSubmit = document.getElementById("route-create-submit");
  const routeCreateCancel = document.getElementById("route-create-cancel");
  const routeCreateSubmitLabel = routeCreateSubmit?.textContent || "Oluştur";

  function buildSuggestedRouteName() {
    const city = activeCityName || "Türkiye";
    const firstStop = selectedLocations[0]?.name || "";
    if (currentUserFirstName && firstStop) {
      return `${currentUserFirstName}'ın ${firstStop} ve Diğer Durakları`;
    }
    if (currentUserFirstName) {
      return `${currentUserFirstName}'ın ${city} Rotası`;
    }
    if (firstStop) {
      return `${firstStop} Rotası`;
    }
    return `${city} Rotası`;
  }

  function updateRouteUI() {
    const count = selectedLocations.length;
    if (routeCountEl) routeCountEl.textContent = `${count} Durak`;
    if (routeClearBtn) routeClearBtn.classList.toggle("hidden", count === 0);
    if (createRouteButton) {
      const isLoggedIn =
        window.__IS_LOGGED_IN__ === true || window.__IS_LOGGED_IN__ === "true";
      if (!isLoggedIn) {
        createRouteButton.disabled = true;
        createRouteButton.classList.add("opacity-60", "cursor-not-allowed");
      } else if (count > 0) {
        createRouteButton.disabled = false;
        createRouteButton.classList.remove("opacity-60", "cursor-not-allowed");
      } else {
        createRouteButton.disabled = true;
        createRouteButton.classList.add("opacity-60", "cursor-not-allowed");
      }
    }
    // hint text
    const hint = document.getElementById("route-hint");
    const isLoggedIn =
      window.__IS_LOGGED_IN__ === true || window.__IS_LOGGED_IN__ === "true";
    if (hint) {
      if (!isLoggedIn) {
        hint.innerHTML = `Rota oluşturmak için lütfen <a href="auth.php?next=${encodeURIComponent(window.location.pathname + window.location.search)}" class="underline">giriş yapın</a>`;
      } else if (selectedLocations.length === 0) {
        hint.textContent = "Rota oluşturmak için bir yer seçin.";
      } else {
        hint.textContent = "";
      }
    }
  }

  routeClearBtn?.addEventListener("click", () => {
    selectedLocations = [];
    document
      .querySelectorAll("[data-location-id].selected")
      .forEach((el) => el.classList.remove("selected"));
    markerMap.forEach((m) => {
      try {
        if (m && m.setLatLng) {
          // nothing to do, leave original markers
        }
      } catch (e) {}
    });
    updateRouteUI();
  });

  // delegate click on location cards to toggle selection
  document.addEventListener("click", (e) => {
    const card = e.target.closest("[data-location-id]");
    if (!card || e.target.closest("a")) {
      return;
    }
    if (card && card.dataset.locationId) {
      const id = Number(card.dataset.locationId);
      const exists = selectedLocations.find((s) => s.id === id);
      if (exists) {
        selectedLocations = selectedLocations.filter((s) => s.id !== id);
        card.classList.remove("selected");
        const mk = markerMap.get(id);
        if (mk && mk.unbindTooltip) {
          try {
            mk.unbindTooltip();
          } catch (e) {}
        }
      } else {
        const loc = allLocations.find((l) => Number(l.id) === id);
        if (loc) {
          selectedLocations.push({ id: loc.id, name: loc.name });
          card.classList.add("selected");
          const mk = markerMap.get(id);
          if (mk && mk.bindTooltip) {
            // we'll add tooltip showing order after updating selection order
          }
        }
      }
      updateRouteUI();
      refreshSelectionBadges();
    }
  });

  // update badges and marker tooltips after selection changes
  function refreshSelectionBadges() {
    // remove existing badges
    document
      .querySelectorAll(".route-order-badge")
      .forEach((el) => el.remove());
    // update order tooltips on markers
    markerMap.forEach((m, key) => {
      try {
        if (m && m.unbindTooltip) m.unbindTooltip();
      } catch (e) {}
    });
    selectedLocations.forEach((s, idx) => {
      const card = document.querySelector(`[data-location-id="${s.id}"]`);
      if (card) {
        const badge = document.createElement("span");
        badge.className =
          "route-order-badge absolute left-3 top-3 bg-primary text-on-primary text-xs font-semibold px-2 py-1 rounded-full";
        badge.textContent = String(idx + 1);
        card.querySelector(".relative")?.appendChild(badge);
      }
      const mk = markerMap.get(s.id);
      if (mk && mk.bindTooltip) {
        try {
          mk.bindTooltip(String(idx + 1), {
            permanent: true,
            direction: "top",
            className: "route-stop-label",
          }).openTooltip();
        } catch (e) {}
      }
    });
  }

  function highlightLocationCard(locationId) {
    const card = document.querySelector(`[data-location-id="${locationId}"]`);
    const marker = markerMap.get(Number(locationId));
    if (marker && marker.openPopup) {
      try {
        marker.openPopup();
      } catch (e) {}
    }
    if (card) {
      card.scrollIntoView({ behavior: "smooth", block: "center" });
      card.classList.add("map-highlight");
      setTimeout(() => card.classList.remove("map-highlight"), 2800);
    }
  }

  // open modal
  createRouteButton?.addEventListener("click", (e) => {
    if (createRouteButton.disabled) return;
    if (routeModal) {
      routeCreateError.classList.add("hidden");
      routeCreateName.value = buildSuggestedRouteName();
      routeCreateName.placeholder = `Örneğin: ${buildSuggestedRouteName()}`;
      routeModal.classList.remove("hidden");
      routeModal.classList.add("flex");
    }
  });

  // cancel
  document
    .getElementById("route-create-cancel")
    ?.addEventListener("click", () => {
      if (routeModal) {
        routeModal.classList.add("hidden");
        routeModal.classList.remove("flex");
      }
    });

  // submit create route
  routeCreateForm?.addEventListener("submit", async (ev) => {
    ev.preventDefault();
    routeCreateError.classList.add("hidden");
    const name = routeCreateName.value.trim();
    const isPublic = Number(routeCreatePublic.value) ? 1 : 0;
    if (!name) {
      routeCreateError.textContent = "Rota adı gerekli.";
      routeCreateError.classList.remove("hidden");
      return;
    }
    if (routeCreateSubmit) {
      routeCreateSubmit.disabled = true;
      routeCreateSubmit.textContent = "Oluşturuluyor...";
    }

    const payload = new FormData();
    payload.append("route_name", name);
    payload.append("is_public", isPublic);
    payload.append("city_id", activeCityId || "");
    payload.append("csrf_token", window.__CSRF_TOKEN__ || "");
    selectedLocations.forEach((s) => payload.append("stops[]", s.id));

    try {
      const res = await fetch(`${apiBase}/routes/create.php`, {
        method: "POST",
        credentials: "same-origin",
        body: payload,
      });
      const text = await res.text();
      let data = null;
      try {
        data = JSON.parse(text);
      } catch (e) {
        // server returned non-json (warnings/errors)
        console.warn("Non-JSON response from create route:", text);
        routeCreateError.classList.remove("hidden");
        routeCreateError.classList.remove("text-primary");
        routeCreateError.classList.add("text-error");
        routeCreateError.textContent = text || "Beklenmeyen sunucu yanıtı.";
        return;
      }

      if (data && data.status === "success") {
        const successMessage =
          data && typeof data.message === "string" && data.message
            ? data.message
            : data && data.data && typeof data.data.message === "string"
              ? data.data.message
              : "Rotanız başarıyla oluşturuldu.";

        showRouteMessage(successMessage, "success");

        const createdRouteId =
          data.data && data.data.route_id ? data.data.route_id : null;
        if (createdRouteId) {
          setTimeout(() => {
            window.location.href = `route-detail.php?id=${createdRouteId}`;
          }, 1400);
        }
      } else {
        let msg = "Rota oluşturulamadı.";
        if (data && typeof data.message === "string" && data.message) {
          msg = data.message;
        } else if (data && data.data && typeof data.data.message === "string") {
          msg = data.data.message;
        } else if (data && data.errors) {
          if (typeof data.errors === "string") {
            msg = data.errors;
          } else if (Array.isArray(data.errors)) {
            msg = data.errors.join(" ");
          } else if (typeof data.errors === "object") {
            msg = Object.values(data.errors).flat().filter(Boolean).join(" ");
          }
        }
        showRouteMessage(msg, "error");
      }
    } catch (err) {
      console.error(err);
      showRouteMessage("Sunucuya bağlanılamadı.", "error");
    } finally {
      if (routeCreateSubmit) {
        routeCreateSubmit.disabled = false;
        routeCreateSubmit.textContent = routeCreateSubmitLabel;
      }
    }
  });

  function showRouteMessage(text, type) {
    if (!routeCreateError) return;
    routeCreateError.classList.remove("hidden");
    routeCreateError.textContent = text;
    if (type === "success") {
      routeCreateError.classList.remove("text-error");
      routeCreateError.classList.add("text-primary");
    } else {
      routeCreateError.classList.remove("text-primary");
      routeCreateError.classList.add("text-error");
    }
  }
  // initialize route UI state
  updateRouteUI();
});
