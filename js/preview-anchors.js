anchors.options = {
	placement: 'right',
	visible: 'always',
	icon: ''
};
anchors.add(".content-wrapper .section-header");
generateTableOfContents(anchors.elements);

function generateTableOfContents(els) {
  var anchoredElText,
    anchoredElHref,
    ol = document.createElement('OL');

  document.getElementById('table-of-contents').appendChild(ol);

  for (var i = 0; i < els.length; i++) {
    anchoredElText = els[i].textContent;
    anchoredElHref = els[i].querySelector('.anchorjs-link').getAttribute('href');
    addNavItem(ol, anchoredElHref, anchoredElText);
  }
}

function addNavItem(ol, href, text) {
  var listItem = document.createElement('LI'),
    anchorItem = document.createElement('A'),
    textNode = document.createTextNode(text);

  anchorItem.href = href;
  ol.appendChild(listItem);
  listItem.appendChild(anchorItem);
  anchorItem.appendChild(textNode);
}