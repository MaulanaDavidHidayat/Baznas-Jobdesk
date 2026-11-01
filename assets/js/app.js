document.addEventListener("DOMContentLoaded", function () {
  const sidebar = document.getElementById("sidebar");
  const toggleButton = document.querySelector("[data-sidebar-toggle]");

  if (sidebar && toggleButton) {
    const collapsedKey = "baznas-sidebar-collapsed";

    if (
      window.innerWidth > 768 &&
      window.localStorage.getItem(collapsedKey) === "1"
    ) {
      document.body.classList.add("sidebar-collapsed");
    }

    toggleButton.addEventListener("click", function () {
      if (window.innerWidth <= 768) {
        sidebar.classList.toggle("sidebar-open");
        return;
      }

      document.body.classList.toggle("sidebar-collapsed");

      window.localStorage.setItem(
        collapsedKey,
        document.body.classList.contains("sidebar-collapsed") ? "1" : "0"
      );
    });

    document.addEventListener("click", function (event) {
      const clickedInsideSidebar = sidebar.contains(event.target);

      const clickedToggle = toggleButton.contains(event.target);

      if (window.innerWidth <= 768 && !clickedInsideSidebar && !clickedToggle) {
        sidebar.classList.remove("sidebar-open");
      }
    });
  }

  // Konfirmasi sebelum menghapus data atau keluar akun.
  document.querySelectorAll("[data-confirm]").forEach(function (element) {
    element.addEventListener("click", function (event) {
      const message = element.dataset.confirm;

      if (!window.confirm(message)) {
        event.preventDefault();
      }
    });
  });

  // Tombol untuk menampilkan atau menyembunyikan password.
  document
    .querySelectorAll("[data-password-toggle]")
    .forEach(function (button) {
      button.addEventListener("click", function () {
        const inputId = button.dataset.passwordToggle;
        const input = document.getElementById(inputId);

        if (!input) {
          return;
        }

        const isPassword = input.type === "password";

        input.type = isPassword ? "text" : "password";

        button.textContent = isPassword ? "Sembunyikan" : "Tampilkan";
      });
    });

  // Tampilkan nominal rupiah secara otomatis di bawah input jumlah.
  function formatRupiah(value) {
    const raw = String(value).replace(/,/g, ".");
    const parts = raw.split(".");
    const integer = parts[0].replace(/\D/g, "");
    const decimal = (parts[1] || "").replace(/\D/g, "");

    const formattedInteger =
      integer.replace(/\B(?=(\d{3})+(?!\d))/g, ".") || "0";

    return "Rp " + formattedInteger + (decimal ? "," + decimal : "");
  }

  document.querySelectorAll("[data-amount-preview]").forEach(function (span) {
    const input = document.getElementById(span.dataset.amountPreview);

    if (!input) {
      return;
    }

    const updatePreview = function () {
      span.textContent = formatRupiah(input.value);
    };

    input.addEventListener("input", updatePreview);
    updatePreview();
  });

  // Terapkan filter bulan secara otomatis saat pilihan berubah.
  document.querySelectorAll("[data-auto-filter]").forEach(function (select) {
    select.addEventListener("change", function () {
      if (select.form) {
        select.form.submit();
      }
    });
  });

  // Tombol cetak laporan.
  document.querySelectorAll("[data-print]").forEach(function (button) {
    button.addEventListener("click", function () {
      window.print();
    });
  });
});
