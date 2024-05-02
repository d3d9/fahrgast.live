import { Modal } from "bootstrap";

let businessCheckInput  = $("#business_check");
let businessSelectInput  = $("#chain_business_select");
let likertRIRadios  = $("#likert_reliability_importance input:radio");
let likertPRRadios  = $("#likert_planned_for_reliability input:radio");
let statusNotTakenReasonWrap = document.getElementById("form-status-not-taken-reason-edit-wrap");
let statusNotTakenReasonSelectInput  = $("#status_not_taken_reason");

let statusTakenRadios = $("#status_taken input:radio");
statusTakenRadios.on('change', updateTaken);
function updateTaken() {
    if (document.forms['status-taken'].elements.taken.value == '0') {
        statusNotTakenReasonWrap.classList.remove('d-none');
        statusNotTakenReasonSelectInput.attr('required', 'required');
    } else {
        statusNotTakenReasonWrap.classList.add('d-none');
        statusNotTakenReasonSelectInput.removeAttr('required');
    }
}

let finishedNotNullWrap = document.getElementById("finished_not_null_wrap");
let finishedArrivedWrap = document.getElementById("finished_arrived_wrap");
let likertFPRadios = $("#likert_felt_punctual input:radio");
let likertFSRadios = $("#likert_felt_stressed input:radio");
let chainFinishedSelectInput = $("#chain_finished");
chainFinishedSelectInput.on('change', updateFinished);
function updateFinished() {
	console.log(this.value);
    if (this.value == '') {
        finishedNotNullWrap.classList.add('d-none');
        finishedArrivedWrap.classList.add('d-none');
        likertFPRadios.removeAttr('required');
        likertFSRadios.removeAttr('required');
    } else {
        finishedNotNullWrap.classList.remove('d-none');
        likertFSRadios.attr('required', 'required');
        if(this.value.startsWith("arrived")) {
            finishedArrivedWrap.classList.remove('d-none');
            likertFPRadios.attr('required', 'required');
        } else { // duplicate code :<
            finishedArrivedWrap.classList.add('d-none');
            likertFPRadios.removeAttr('required');
        }
    }
}

let editStatusContext   = document.getElementById("edit-status-context");
let editTakenContext    = document.getElementById("edit-status-taken-context");
let editDestinationContext = document.getElementById("edit-status-destination-context");

let businessButton      = $("#businessDropdownButton");
const businessIcons     = ["fa-user", "fa-briefcase", "fa-building"];
let visibilityFormInput = $("#checkinVisibility");
let visibilityButton    = $("#visibilityDropdownButton");
const visibilityIcons   = ["fa-globe-americas", "fa-lock-open", "fa-user-friends", "fa-lock", "fa-user-check"];
let chainSelect         = $("#form-status-chain-edit");

// FGL: Idee Fallback Standardvalue verworfen, reicht in chain-dropdown Include. Beim Editen wird es ja bereits einen Wert geben, weil erzwungen.

function setIconForDropdown(value, button, inputFieldValue, icons) {
    let number  = parseInt(value, 10);
    let classes = button.children()[0].classList;
    icons.forEach((value) => {
        classes.remove(value);
    });
    classes.add(icons[number]);
    inputFieldValue.val(number);
}

$(".trwl-business-item").on("click", function (event) {
    setIconForDropdown(event.currentTarget.dataset.trwlBusiness, businessButton, businessCheckInput, businessIcons);
});

$(".trwl-visibility-item").on("click", function (event) {
    setIconForDropdown(event.currentTarget.dataset.trwlVisibility, visibilityButton, visibilityFormInput, visibilityIcons);
});

window.statusContextString = function(dataset) {
    return `Fahrt mit ${dataset.trwlLinename} von ${dataset.trwlOrigin} (${dataset.trwlDeparturePlanned}) nach ${dataset.trwlDestination} (${dataset.trwlArrivalPlanned})`;
}

$(document).on("click", ".edit", function (event) {
    event.preventDefault();

    let statusId = event.currentTarget.dataset.trwlStatusId;
    let dataset  = document.getElementById("status-" + statusId).dataset;

    editStatusContext.innerHTML = statusContextString(dataset);

    document.querySelector("#status-update input[name='statusId']").value = statusId;
    document.querySelector("#status-update textarea[name='body']").value  = dataset.trwlStatusBody;

    document.getElementById("edit-status-manual-context-origin").innerHTML = dataset.trwlOrigin;
    document.getElementById("edit-status-manual-context-planned-departure").innerHTML = dataset.trwlDeparturePlanned;
    document.getElementById("edit-status-manual-context-real-departure").innerHTML = dataset.trwlDepartureReal || "&mdash;";
    document.getElementById("edit-status-manual-context-destination").innerHTML = dataset.trwlDestination;
    document.getElementById("edit-status-manual-context-planned-arrival").innerHTML = dataset.trwlArrivalPlanned;
    document.getElementById("edit-status-manual-context-real-arrival").innerHTML = dataset.trwlArrivalReal || "&mdash;";

    document.querySelector("#status-update input[name='manualDeparture']").value  = dataset.trwlManualDeparture;
    document.querySelector("#status-update input[name='manualArrival']").value  = dataset.trwlManualArrival;

    let statusBusiness   = dataset.trwlBusinessId;
    let statusVisibility = dataset.trwlVisibility;
    businessCheckInput.val(statusBusiness);
    visibilityFormInput.val(statusVisibility);
    if (businessButton.length) setIconForDropdown(statusBusiness, businessButton, businessCheckInput, businessIcons);
    if (visibilityButton.length) setIconForDropdown(statusVisibility, visibilityButton, visibilityFormInput, visibilityIcons);

    if (!chainSelect.find('option[value="' + dataset.trwlChainId + '"]').length) {
        $("#form-status-chain-recents-edit").append($('<option>', {value: dataset.trwlChainId, text: dataset.trwlChainLabel}));
    }
    chainSelect.val(dataset.trwlChainId);

    document.querySelector("#status-update input[name='planned']").checked  = +dataset.trwlPlanned;

    const modal = new Modal($("#edit-modal"));
    modal.show();
    document.querySelector('#body-length').innerText = document.querySelector('#status-body').value.length;
});

$(document).on("click", ".edit-destination", function (event) {
    event.preventDefault();

    let statusId = event.currentTarget.dataset.trwlStatusId;
    let dataset  = document.getElementById("status-" + statusId).dataset;

    document.querySelector("#status-update-destination input[name='statusId']").value = statusId;

    editDestinationContext.innerHTML = statusContextString(dataset);

    // FGLTODO-LP check dataset chain, planned, taken, show infos was passieren wird.

    //Clear list
    document.querySelector("#status-update-destination select[name='destinationStopoverId']").innerHTML = "";

    let alternativeDestinations = JSON.parse(dataset.trwlAlternativeDestinations);
    if (alternativeDestinations) {
        document.querySelector('.destination-wrapper').classList.remove('d-none');
        for (let destId in alternativeDestinations) {
            let dest            = alternativeDestinations[destId];
            let stopoverId      = dest.id;
            let stopoverName    = dest.name;
            let stopoverArrival = dest.arrival_planned;

            let stopoverOption   = document.createElement("option");
            stopoverOption.value = stopoverId;
            stopoverOption.text  = stopoverArrival + ': ' + stopoverName;
            document.querySelector("#status-update-destination select[name='destinationStopoverId']").appendChild(stopoverOption);
        }
        document.querySelector("#status-update-destination select[name='destinationStopoverId']").value = dataset.trwlDestinationStopover;
    } else {
        document.querySelector('.destination-wrapper').classList.add('d-none');
    }

    const modal = new Modal($("#edit-destination-modal"));
    modal.show();
});

$(document).on("click", ".edit-taken", function (event) {
    event.preventDefault();

    let statusId = event.currentTarget.dataset.trwlStatusId;
    let dataset  = document.getElementById("status-" + statusId).dataset;

    document.querySelector("#status-taken input[name='statusId']").value = statusId;

    editTakenContext.innerHTML = statusContextString(dataset);

    statusTakenRadios.val([dataset.trwlTaken]);
    statusNotTakenReasonSelectInput.val(dataset.trwlNotTakenReason);

    updateTaken();

    const modal = new Modal($("#status-taken-modal"));
    modal.show();
});

$(document).on("click", ".edit-chain", function (event) {
    event.preventDefault();

    let chainId = event.currentTarget.dataset.trwlChainId;
    let dataset  = document.getElementById("chain-" + chainId).dataset;

    document.querySelector("#chain-update input[name='chainId']").value = chainId;
    document.querySelector("#chain-update input[name='title']").value  = dataset.trwlChainTitle;
    document.querySelector("#chain-update textarea[name='body']").value  = dataset.trwlChainBody;

    let chainBusiness   = dataset.trwlBusinessId;
    businessSelectInput.val(chainBusiness);

    likertRIRadios.val([dataset.fglReliabilityImportance]);
    likertPRRadios.val([dataset.fglPlannedForReliability]);

    const modal = new Modal($("#chain-edit-modal"));
    modal.show();
    document.querySelector('#chain-title-length').innerText = document.querySelector('#chain-title').value.length;
    document.querySelector('#chain-body-length').innerText = document.querySelector('#chain-body').value.length;
});

$(document).on("click", ".finish-chain", function (event) {
    event.preventDefault();

    let chainId = event.currentTarget.dataset.trwlChainId;
    let dataset  = document.getElementById("chain-" + chainId).dataset;

    document.querySelector("#chain-finish input[name='chainId']").value = chainId;
    document.getElementById("ddd-value").innerHTML = dataset.fglDdd ?? '&mdash;';
    document.getElementById("chain-finish-ddd-context").style.display = !!dataset.fglDdd ? '' : 'none';

    chainFinishedSelectInput.val(dataset.fglFinished);
    likertFPRadios.val([dataset.fglFeltPunctual]);
    likertFSRadios.val([dataset.fglFeltStressed]);

    chainFinishedSelectInput.trigger('change');

    const modal = new Modal($("#chain-finish-modal"));
    modal.show();
});

$(document).on("click", ".submitRelRef", function (event) {
    // event.preventDefault();
    let dataset = event.currentTarget.dataset;
    let input = document.getElementById(dataset.rel + "Ref");
    input.value = dataset.ref;
    input.disabled = false;
    document.getElementById("journey-form").submit();
});
