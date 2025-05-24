document.getElementById("year").textContent = new Date().getFullYear();

function initMap() {
  const plageBelge = [36.8475, 11.0939];
  const map = L.map("map").setView(plageBelge, 19);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution:
      '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
  }).addTo(map);
  L.tileLayer(
    "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
    {
      attribution: "Tiles © Esri — Source: Esri, Maxar, Earthstar Geographics",
      maxZoom: 19,
    }
  ).addTo(map);
  const paddleIcon = L.icon({
    iconUrl: "https://cdn-icons-png.flaticon.com/512/2972/2972035.png",
    iconSize: [40, 40],
    iconAnchor: [20, 40],
    popupAnchor: [0, -40],
  });
  const marker = L.marker(plageBelge, {
    icon: paddleIcon,
    title: "Paddle Kelibia Summer",
    alt: "Emplacement de location de paddle",
    riseOnHover: true,
  }).addTo(map);
  marker
    .bindPopup(
      `
                <div style="text-align:center;">
                    <b style="color:${getComputedStyle(
                      document.documentElement
                    ).getPropertyValue(
                      "--primary-color"
                    )}">Paddle Kelibia Summer</b><br>
                    <small>Location de paddle gonflable</small><br><br>
                    <img src="pad.jpg" style="max-width:100%; border-radius:5px;"><br>
                    <div style="margin-top:10px;">
                        <i class="fas fa-map-marker-alt" style="color:${getComputedStyle(
                          document.documentElement
                        ).getPropertyValue("--accent-color")}"></i> 
                        Plage El Belge, Kelibia<br>
                        <small>(Zone des activités nautiques)</small>
                    </div>
                    <div style="margin-top:10px;">
                        <a href="https://www.google.com/maps/dir//36.8475,11.0939" target="_blank" style="background:${getComputedStyle(
                          document.documentElement
                        ).getPropertyValue(
                          "--secondary-color"
                        )}; color:white; padding:5px 10px; border-radius:5px; text-decoration:none; display:inline-block; margin-top:5px;">
                            <i class="fas fa-directions"></i> Itinéraire
                        </a>
                    </div>
                </div>
            `
    )
    .openPopup();
  L.circle(plageBelge, {
    color: getComputedStyle(document.documentElement).getPropertyValue(
      "--secondary-color"
    ),
    fillColor: getComputedStyle(document.documentElement).getPropertyValue(
      "--secondary-color"
    ),
    fillOpacity: 0.2,
    radius: 20,
  })
    .addTo(map)
    .bindTooltip("Notre stand de location", {
      permanent: false,
      direction: "top",
    });
  const beachArea = [
    [36.8478, 11.0935],
    [36.8472, 11.0943],
    [36.8469, 11.0938],
    [36.8475, 11.093],
  ];
  L.polygon(beachArea, {
    color: "#f1c40f",
    fillColor: "#f39c12",
    fillOpacity: 0.1,
    weight: 2,
  })
    .addTo(map)
    .bindTooltip("Zone de plage El Belge", { permanent: false });
  L.control
    .layers(
      {
        Carte: L.tileLayer(
          "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        ),
        "Vue satellite": L.tileLayer(
          "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}"
        ),
      },
      null,
      { position: "topright" }
    )
    .addTo(map);
  L.control.scale({ position: "bottomleft" }).addTo(map);
}

document.addEventListener("DOMContentLoaded", function () {
  initMap();

  const today = new Date().toISOString().split("T")[0];
  document.getElementById("date").min = today;

  const paddleCount = document.getElementById("paddle-count");
  const decreaseBtn = document.getElementById("decrease-paddle");
  const increaseBtn = document.getElementById("increase-paddle");
  const discountNotice = document.getElementById("group-discount");
  const telInput = document.getElementById("tel");

  decreaseBtn.addEventListener("click", function () {
    if (parseInt(paddleCount.value) > 1) {
      paddleCount.value = parseInt(paddleCount.value) - 1;
      updateDiscountNotice();
    }
  });

  increaseBtn.addEventListener("click", function () {
    if (parseInt(paddleCount.value) < 10) {
      paddleCount.value = parseInt(paddleCount.value) + 1;
      updateDiscountNotice();
    }
  });

  paddleCount.addEventListener("change", function () {
    if (this.value < 1) this.value = 1;
    if (this.value > 10) this.value = 10;
    updateDiscountNotice();
  });

  telInput.addEventListener("input", function () {
    if (!this.value.match(/^[0-9]{8}$/)) {
      this.setCustomValidity(
        "Veuillez entrer un numéro de téléphone à 8 chiffres."
      );
    } else {
      this.setCustomValidity("");
    }
  });

  function updateDiscountNotice() {
    if (parseInt(paddleCount.value) >= 3) {
      discountNotice.style.display = "block";
    } else {
      discountNotice.style.display = "none";
    }
  }

  document
    .getElementById("reservationForm")
    .addEventListener("submit", function (event) {
      event.preventDefault();

      const formData = new FormData(this);
      const data = {};
      formData.forEach((value, key) => {
        data[key] = value;
      });

      const hours = data["hours"];
      const paddleCount = parseInt(data["paddle-count"]);
      let basePrice = 0;
      let durationText = "";
      let discount = 0;

      switch (hours) {
        case "1":
          basePrice = 20;
          durationText = "1 heure";
          break;
        case "2":
          basePrice = 35;
          durationText = "2 heures";
          break;
        case "3":
          basePrice = 50;
          durationText = "3 heures";
          break;
        case "4":
          basePrice = 70;
          durationText = "4 heures";
          break;
        case "full":
          basePrice = 120;
          durationText = "Journée complète";
          break;
      }

      let groupDiscountApplied = false;
      if (paddleCount >= 3) {
        discount += 0.1;
        groupDiscountApplied = true;
      }

      const totalPrice = basePrice * paddleCount * (1 - discount);
      const savings = basePrice * paddleCount - totalPrice;

      const detailsDiv = document.getElementById("reservationDetails");
      detailsDiv.innerHTML = `
                <p><span>Nom:</span> <span>${data["prenom"]} ${
        data["nom"]
      }</span></p>
                <p><span>Téléphone:</span> <span>${data["tel"]}</span></p>
                <p><span>Date:</span> <span>${data["date"]} à ${
        data["time"]
      }</span></p>
                <p><span>Durée:</span> <span>${durationText}</span></p>
                <p><span>Nombre de paddles:</span> <span>${paddleCount}</span></p>
                <p><span>Message:</span> <span>${
                  data["message"] || "Aucun"
                }</span></p>
                ${
                  groupDiscountApplied
                    ? '<p><span style="color:var(--promo-color);">Promotion groupe:</span> <span>-10%</span></p>'
                    : ""
                }
                ${
                  savings > 0
                    ? `<p><span>Économies:</span> <span>${savings.toFixed(
                        2
                      )} DT</span></p>`
                    : ""
                }
            `;

      document.getElementById(
        "totalPrice"
      ).innerHTML = `Total: <span style="font-size:1.3em;">${totalPrice.toFixed(
        2
      )} DT</span>`;

      document.getElementById("confirmationModal").style.display = "flex";
      const formAction = "./send.php";

      fetch(formAction, {
        method: "POST",
        body: formData,
        headers: {
          Accept: "application/json",
        },
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then((result) => {
          const formMessage = document.getElementById("formMessage");
          formMessage.style.display = "block";
          if (result.status === "success") {
            formMessage.className = "form-message success";
            formMessage.textContent = "Réservation enregistrée avec succès !";
            this.reset();
            paddleCount.value = 1;
            updateDiscountNotice();
            document.getElementById("date").min = new Date()
              .toISOString()
              .split("T")[0];
            const closeModalBtn = document.getElementById("closeModal");
            closeModalBtn.onclick = function () {
              document.getElementById("confirmationModal").style.display =
                "none";
              document.getElementById("successModal").style.display = "flex";
            };
          } else {
            formMessage.className = "form-message error";
            formMessage.textContent = "Erreur: " + result.message;
            document.getElementById("confirmationModal").style.display = "none";
          }
        })
        .catch((error) => {
          const formMessage = document.getElementById("formMessage");
          formMessage.style.display = "block";
          formMessage.className = "form-message error";
          formMessage.textContent = "Erreur lors de l'envoi: " + error.message;
          document.getElementById("confirmationModal").style.display = "none";
        });
    });

  document
    .getElementById("closeSuccessModal")
    .addEventListener("click", function () {
      document.getElementById("successModal").style.display = "none";
    });

  document.querySelectorAll(".sidebar-nav a").forEach((link) => {
    link.addEventListener("click", function () {
      document
        .querySelectorAll(".sidebar-nav a")
        .forEach((el) => el.classList.remove("active"));
      this.classList.add("active");
    });
  });
});
