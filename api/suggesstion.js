function showHint(str) {
  if (str.length == 0) {
    document.getElementById("txtHint").innerHTML = "";
    return;
  }

  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      var suggestions = JSON.parse(this.responseText);
      var hint = "";

      if (suggestions.length === 0) {
        hint = "<p><em>Tidak ditemukan kosan yang sesuai.</em></p>";
      } else {
        hint = "<ul style='list-style: none; padding: 0;'>";

        for (var i = 0; i < suggestions.length; i++) {
          hint += `
            <li style="margin-bottom: 10px; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
              <strong>${suggestions[i].name}</strong><br>
              <small>${suggestions[i].city}, ${suggestions[i].province}</small>
            </li>
          `;
        }

        hint += "</ul>";
      }

      document.getElementById("txtHint").innerHTML = hint;
    }
  };

  // Sesuaikan parameter dengan PHP kamu (pakai q)
  xhttp.open("GET", "search-suggestion.php?q=" + encodeURIComponent(str), true);
  xhttp.send();
}
