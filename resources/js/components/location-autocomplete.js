(function () {
    const inputOrigin     = document.getElementById("origin-autocomplete");
    const containerOrigin = document.getElementById("origin-autocomplete-container");

    const inputDestination     = document.getElementById("destination-autocomplete");
    const containerDestination = document.getElementById("destination-autocomplete-container");

    const journeyForm = document.getElementById("journey-form");

    if (inputOrigin == null || inputDestination == null || journeyForm == null) {
        return;
    }

    window.awesompleteOrigin = new Awesomplete(inputOrigin, {
        minChars: 2,
        autoFirst: true,
        sort: false,
        list: [],
        filter: () => true,
        replace: function(suggestion) {
	    this.input.value = suggestion.label;
	},
        container: function () {
            containerOrigin.classList.add("awesomplete");
            return containerOrigin;
        }
    });

    window.awesompleteDestination = new Awesomplete(inputDestination, {
        minChars: 2,
        autoFirst: true,
        sort: false,
        list: [],
        filter: () => true,
        replace: function(suggestion) {
	    this.input.value = suggestion.label;
	},
        container: function () {
            containerDestination.classList.add("awesomplete");
            return containerDestination;
        }
    });

    journeyForm.addEventListener("submit", function(event) {
        let valid = true;
        [inputOrigin, inputDestination].forEach(inp => {
            if (!getHiddenValueInput(inp).value) {
                inp.setCustomValidity('Bitte ein Suchergebnis aus der Liste auswählen.');
                valid = false;
            }
        });
        if (!valid) {
            event.preventDefault();
        }
    });

    function debounce(func, timeout = 300) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                func.apply(this, args);
            }, timeout);
        };
    }

    function locationData(location) {
        let v, l, myType;
        if (location.type === "station" || location.type === "stop") {
            v = `${location.type}|${location.id}`;
            l = location.name;
            myType = "transport";
        } else {
            if (location.poi) {
                v = `poi|${location.id}|${location.latitude}|${location.longitude}`;
                l = location.name;
                myType = "poi";
            } else {
                v = `address|${location.latitude}|${location.longitude}|${location.address}`;
                l = location.address;
                myType = "address";
            }
        }
	return {
            value: v,
            label: l,
            type: myType // FGLTODO: nutzen um icon darzustellen? ...
        };
    }

    function getHiddenValueInput(input) {
        return document.getElementById(input.id + "-value");
    }

    function fetchLocations(destination = false) {
        let input = destination ? inputDestination : inputOrigin;
        let awesomplete = destination ? window.awesompleteDestination : window.awesompleteOrigin;
        getHiddenValueInput(awesomplete.input).value = '';

        if (input.value.length < 2) return;

        fetch("/transport/location/autocomplete/" + encodeURI(input.value))
            .then(res => res.json())
            .then(json => {
                awesomplete.list = json.map(locationData);
                awesomplete.evaluate();
            });
    }

    const oD = debounce(() => fetchLocations(false));
    const dD = debounce(() => fetchLocations(true));
    inputOrigin.addEventListener("input", () => { inputOrigin.setCustomValidity(''); oD(); });
    inputDestination.addEventListener("input", () => { inputDestination.setCustomValidity(''); dD(); });

    function selectLocation(data) {
        getHiddenValueInput(data.srcElement).value = data.text.value;
    }

    inputOrigin.addEventListener("awesomplete-select", selectLocation);
    inputDestination.addEventListener("awesomplete-select", selectLocation);

})();
