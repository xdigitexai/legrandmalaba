// function name
function random_bg() {
  // hexadecimal starting symbol
  var color = "#";
  // Set your colors here
  var letters = ["e51c23", "e91e63", "9c27b0", "673ab7", "ff5177", "ff4081", "e040fb", "7c4dff", "3f51b5", "5677fc", "03a9f4", "00bcd4", "536dfe", "6889ff", "40c4ff", "009688", "259b24", "8bc34a", "ff9800", "ff5722", "ffab40", "ff6e40", "c5d5cb", "9fa8a3", "b3c2bf", "3b3a36", "c9d8c5", "a8b6bf", "7d4627", "c9c9c9", "9ad3de", "89bdd3", "6ed3cf", "9068be", "e62739", "3fb0ac", "173e43", "e6cf8b", "b56969", "22264b", "98dafe", "daad86", "312c32", "b0aac2", "6534ff", "5e0231", "e6af4b", "e05038", "334431", "06000a", "e43235", "667467", "262216", "97743a", "431c5d", "e05915", "cdd422"];
  // Select random color to be assigned
  color += letters[Math.floor(Math.random() * letters.length)];
  // Setting the random color on your HTML element.
  document.body.style.background = color;
}