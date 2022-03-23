---
---
var BibtexCopyAnimation;

function copyBibtex(id) {
  navigator.clipboard.writeText(
    document.getElementById("bibtex-" + id).innerHTML
  );
  const copiedMessages = document.getElementsByClassName("copied-message");
  for (let i = 0; i < copiedMessages.length; i++) {
    let message = copiedMessages[i];
    message.style.display
      = (message.id == "copied-" + id)
      ? "inline-block"
      : "none";

    if (message.id == "copied-" + id) {
      if (BibtexCopyAnimation) {
        BibtexCopyAnimation.cancel();
      }
      BibtexCopyAnimation = message.animate(
        [
          {
            opacity: 1
          }
        , {
            opacity: 0
          }
        ]
      , {
          delay: 2000
        , duration: 2000
        , fill: "forwards"
        }
      );
    }
  }
}
