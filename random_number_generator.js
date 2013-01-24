// To show/hide an additional help
function random_number_generator_switchVisibility(eltId) {
	var elt = document.getElementById(eltId);
	if(elt.style.display == 'block')
		elt.style.display = 'none';
	else
		elt.style.display = 'block';
}
