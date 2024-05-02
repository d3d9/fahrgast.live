@extends('layouts.app')

@section('title', 'Über')
@section('meta-robots', 'index')
@section('meta-description', "ÖPNV-Erhebungsanwendung")
@section('canonical', route('static.about'))

@section('content')
    <div class="px-4 py-5 mt-n4 mb-4 profile-banner">
        <div class="container">
            <div class="text-white">
                <h1>Über fahrgast.live <br/><span class="fs-3"></span></h1>
                <?php /*
                <hr/>
                <div class="btn-group">
                    <a href="https://github.com/Traewelling/traewelling/issues/new?assignees=&labels=bug%2CTo+Do&template=bug_report.md"
                       target="_blank" class="btn btn-sm btn-danger">
                        <i class="fa-solid fa-bug"></i>
                        {{__('report-bug')}}
                    </a>
                    <a href="https://github.com/Traewelling/traewelling/issues/new?assignees=&labels=enhancement&template=feature_request.md&title="
                       target="_blank" class="btn btn-sm btn-success">
                        <i class="fa-solid fa-plus"></i>
                        {{__('request-feature')}}
                    </a>
                    <a href="{{route('support')}}" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-headset"></i>
                        {{__('to-support')}}
                        @guest
                            {{__('login-required')}}
                        @endguest
                    </a>
                </div>
                */ ?>
            </div>


        </div>
    </div>

    <div class="container" id="about-page">
        <div class="row">
            <div class="col-lg-6">
                <div class="card mb-2">
                    <div class="card-body">
                        <h2 class="fs-4 fw-bold text-trwl">
                            <i class="fa-solid fa-code"></i>
                            Entwicklungsstand
                        </h2>
                        <p class="lead mb-2">
                            Am 19. Februar beginnt die zweiwöchige Erhebungsphase.
                        </p>
                        <p class="lead mb-2">
                            <strong class="fw-bold"><a class="text-decoration-underline" href="https://docs.google.com/document/d/18_llC5ctMzf31_WW-3nr5c9xgq3Vs-vw63pWDng3NCw/edit?usp=sharing">Illustrierte Erläuterungen zur Einrichtung und Nutzung</a></strong><br/>
                            <small class="text-muted fs-6">
                                Die Ansicht am PC wird empfohlen. Bei Darstellungsproblemen am Handy (insbesondere Bilder) können Sie die 
                                <a class="text-decoration-underline" href="/handbuch.pdf">PDF-Version</a> nutzen (nicht optimiert).
                            </small>
                        </p>
                        <?php /*
                        <h3 class="fs-5 fw-bold text-trwl">Letzte Änderungen</h3>
                        <ul>
                            <li>08.02.: u. a. wird nun die Zielhaltestelle von gespeicherten Fahrten dargestellt</strong>
                            <li><strong class="fw-bold">07.02.2024: Finale Testphase</strong>
                                <ul>
                                    <li><strong class="fw-bold">Routing-Funktion eingebaut</strong></li>
                                    <li>Haltestellensuche zeigt mehr grobe Treffer</li>
                                    <li>Formulare verbessert (z. B. Auswahldropdowns durch Radiobuttons ersetzt)</li>
                                    <li>Formulierungen und weitere Kleinigkeiten im UI angepasst</li>
                                    <li>Datenverarbeitung in Bezug auf Echtzeitdaten verbessert</li>
                                    <li><small>Update auf Träwelling commit 7af56ed</small></li>
                                </ul>
                            </li>
                            <!--
                            <li><small>25.12.2023: Update auf Träwelling commit 834d275</small></li>
                            <li><strong class="fw-bold">21.12.2023: Start der ersten Testphase</strong></li>
                            -->
                        </ul> */ ?>
                        <h3 class="fs-5 fw-bold text-trwl">Bekannte Fehler</h3>
                        <ul class="mb-0">
                            <li>Manchmal werden bei der Auswahl der Ausstiegshaltestelle nicht alle Möglichkeiten dargestellt, manchmal kommt der Fehler "Start-ID ist nicht in den Zwischenstopps" &ndash; In diesen Fällen bitte einfach neuladen und ggf. die Auswahl erneut treffen, dann funktioniert alles.</li>
                            <li>Fahrten, die zwischendrin ihre Liniennummer wechseln, werden immer mit der ersten verwendeten dargestellt</li>
                            <!-- <li>&hellip;? <strong class="fw-bold">Fehlermeldungen und Verbesserungsvorschläge bitte jederzeit an mich <a href="/legal" class="text-trwl">melden</a>.</strong></li> -->
                        </ul>
                        <!--
                        <h3 class="fs-5 fw-bold text-trwl">Fehlende Features</h3>
                        <ul class="mb-0">
                            <li>Usability-Verbesserungen
                                <ul class="mb-0">
                                    <li>Während die Seite lädt und noch nicht für Eingaben bereit ist, sowie nach dem Absenden von Formularen, ein Lade-Overlay darstellen</li>
                                    <li>Sich überschneidende Fahrten innerhalb Plan- oder tatsächlicher Reisekette erkennen und warnen</li>
                                </ul>
                            </li>
                            <li>Kartenansicht für Reisekette</li>
                        </ul>
                        -->
                    </div>
                </div>
                <div class="card mb-2">
                    <div class="card-body">
                        <h2 class="fs-4 fw-bold text-trwl">
                            <i class="fa-solid fa-circle-question"></i>
                            Was ist fahrgast.live?
                        </h2>
                        <p class="lead mb-0">
                            Diese Anwendung soll für eine Erhebung der Wahrnehmung der Zuverlässigkeit des ÖPNV eingesetzt werden.
                            Die Teilnehmenden sollen ihre geplanten Fahrten speichern können, und während der Reise angeben, ob sie wie geplant stattfinden konnten oder Abweichungen aufgetreten sind.
                            Da häufig mehrere Fahrten mit Umstiegen nötig sind, werden sie zusammenhängend in <strong class="text-trwl">Reiseketten</strong> erfasst. So kann am Ende die Verspätung am Ziel ermittelt werden, und nicht nur die Verspätung einzelner Fahrten.
                            Zu Beginn und Ende der Reise sollen kurze Fragen in Zusammenhang mit der Zuverlässigkeit beantwortet werden.
                        </p>
                    </div>
                </div>
                <div class="card mb-2">
                    <div class="card-body">
                        <h2 class="fs-4 fw-bold text-trwl">
                            <i class="fa-solid fa-heart"></i>
                            Auf welcher Grundlage funktioniert das?
                        </h2>
                        <p class="lead m-0">
                            Aufgebaut ist diese Anwendung auf dem Code von <strong><a href="https://traewelling.de/about" class="text-trwl" rel="noreferrer noopener" target="_blank">Träwelling</a></strong>, welches wie ein Fahrtenbuch und soziales Netzwerk für Fahrten in öffentlichen Verkehrsmitteln verwendet werden kann.
                            Das funktioniert auf Grundlage von Fahrplan- und Echtzeitdaten aus den Auskunftssystemen der Deutschen Bahn.
                            Für <strong class="text-trwl">fahrgast.live</strong> sind sämtliche sozialen Funktionen deaktiviert worden, die Nutzenden können ihre Daten gegenseitig nicht sehen.
                            Wer Träwelling schon kennt, wird weitere Unterschiede bemerken können, beispielsweise werden auch Ausfälle dargestellt und man kann in sie "einchecken" &ndash; wobei von Einchecken ja nicht mehr die Rede ist, da die Begrifflichkeiten Speichern oder Anlegen sowie die Angabe "Mitgefahren" verwendet werden.
                        </p>
                    </div>
                </div>
                <div class="card mb-2">
                    <div class="card-body">
                        <h2 class="fs-4 fw-bold text-trwl">
                            <i class="fa-solid fa-clipboard-question"></i>
                            Wie läuft die Erhebung ab?
                        </h2>
                        <p class="lead m-0 mb-2">
                            Hier ein Kurzüberblick:
                        </p>
                        <ul class="lead">
                            <li>Vor Beginn der Reisekette
                                <ol class="mb-2">
                                    <li>Abfahrtsmonitor für Starthaltestelle aufrufen</li>
                                    <li>Fahrt auswählen und speichern, neue Reisekette wird angelegt</li>
                                    <li>ggf. wie zuvor die restlichen Fahrten heraussuchen und zur Reisekette hinzufügen</li>
                                    <li>Fehlende Angaben bei der Reisekette ergänzen</li>
                                </ol><!-- routing -->
                            </li>
                            <li>Während der Fahrten
                                <ol class="mb-2">
                                    <li>Zu Beginn jeder Fahrt die Angabe treffen, ob man mitfährt</li>
                                    <li>ggf. alternative Fahrten heraussuchen, speichern, als mitgefahren markieren</li>
                                    <li>Nach dem Ausstieg die Echtzeitdaten überprüfen und ggf. überschreiben</li>
                                </ol><!-- ereignisse -->
                            </li>
                            <li>Am Ende der Reisekette
                                <ol class="mb-2">
                                    <li>Ggf. noch fehlende Angaben und ausstehende Fahrten fertig erfassen, damit keine Aufforderungen mehr vorliegen</li>
                                    <li>Erfassung über den dann sichtbaren Button beenden</li>
                                </ol>
                            </li>
                        </ul>
                        <p class="lead m-0">
                            Die einzelnen Bestandteile werden in den folgenden Abschnitten genauer beschrieben.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card mb-2">
                    <div class="card-body">
                        <h2 class="fs-4 fw-bold text-trwl">
                            <i class="fa-solid fa-train"></i>
                            Wie speichere ich meine Fahrten ab?
                        </h2>
                        <p class="lead m-0 mb-2">
                            Alle <strong class="text-trwl">Fahrten</strong> in einem öffentlichen Verkehrsmittel, ob geplant oder ungeplant, können basierend auf den Fahrplan- und Echtzeitdaten ausgewählt und gespeichert werden.
                            Dies geschieht über die Stationssuche im Dashboard oder den Klick auf einen Haltestellennamen in einer bestehenden Fahrt.
                            Besonders die geplanten Fahrten kann man auch im Voraus bereits speichern.
                            Generell sollten Fahrten spätestens 10 Minuten nach dem Zustieg angelegt worden sein, da eventuell verfügbare Echtzeitdaten sonst nicht mehr vollständig abgerufen werden können.
                        </p>
                        <p class="lead m-0 mb-2">
                            Jede Fahrt ist einer <strong class="text-trwl">Reisekette</strong> zugeordnet, was im nächsten Abschnitt näher beschrieben wird.
                            Beginnend kurz vor der Abfahrt kann über einen Button auf der Fahrt angegeben werden, ob man auch tatsächlich mitfährt, und wenn nicht, aus welchem Grund nicht.
                            Muss man ungeplant auf eine andere Fahrt ausweichen, kann man diese auch wieder im Abfahrtsmonitor heraussuchen, speichern (mit der Angabe, dass sie nicht geplant ist), und schließlich als mitgefahren angeben.
                        </p>
                        <p class="lead m-0">
                            In vielen Fällen liegen Echtzeitdaten für die Fahrten vor, und sie werden in regelmäßigen Abständen automatisch aktualisiert.
                            Sollte dies nicht der Fall sein oder zu ungenau sein, können im Kontextmenü der Abfahrt unter Bearbeiten die Abfahrts- oder Ankunftszeit überschrieben werden.
                        </p>
                    </div>
                </div>
                <div class="card mb-2">
                    <div class="card-body">
                        <h2 class="fs-4 fw-bold text-trwl">
                            <i class="fa-solid fa-timeline"></i>
                            Was ist eine Reisekette?
                        </h2>
                        <p class="lead m-0 mb-2">
                            In einer Reisekette werden alle zusammenhängenden Fahrten gebündelt. So können Reisen von Start bis Ziel inkl. Umstiegen untersucht werden, statt nur die einzelnen Fahrtabschnitte zu betrachten.
                            Jede Fahrt ist einer Reisekette zugeordnet, selbst wenn diese dann planmäßig nur aus einer Fahrt bestehen sollte. Hin- und Rückfahrten sind getrennt zu erfassen, ebenso sollten Reiseketten mit Zwischenzielen, bei denen man sich länger unabhängig vom ÖPNV-System aufhält, lieber aufgeteilt werden.
                            Grundsätzlich müssen zur Reisekette bereits zu Beginn Angaben wie der Wegezweck erfasst werden. Fehlen diese Angaben, wird ein entsprechender Button angezeigt. Außerdem können bei den Reiseketten ein verständlicher Titel und optionale Notizen hinterlegt werden.
                        </p>
                        <p class="lead m-0 mb-2">
                            Wie zuvor beschrieben werden Fahrten im Bezug auf die Reisekette abgespeichert.
                            Jede Reisekette besteht eigentlich aus zweien: einer geplanten und einer tatsächlich gefahrenen.
                            Beim Speichern der Fahrten wird angegeben, ob sie Teil der Plan-Reisekette sind.
                            Während die Reise läuft, trifft man dann die Angaben, ob man tatsächlich mitgefahren ist und fügt ggf. spontan als Alternative genommene Fahrten der Reisekette hinzu.
                            So entwickelt sich zusätzlich zur geplanten Reisekette eine ebenfalls auf der Detailseite dargestellte tatsächliche Reisekette.
                        </p>
                        <p class="lead m-0 mb-2">
                            Geplante Fahrten, die man wegen Abweichungen nicht mitgefahren ist, sollten unter keinen Umständen gelöscht werden, sondern es ist dann die Angabe zu treffen, dass man nicht mitgefahren ist (und aus welchem Grund, z. B. Verspätung, oder man ist schon wegen einer vorherigen Störung von der Plan-Reisekette abgewichen).
                            Gelöscht werden sollten nur Fahrten, die man fälschlicherweise angelegt hat. Die Ausstiegshaltestelle lässt sich im Kontextmenü der Fahrt noch ändern. Hat man aber die falsche Abfahrt (falsche Linie, falsche Uhrzeit etc.) ausgewählt, kann man sie natürlich löschen und die richtige neu speichern.
                        </p>
                        <p class="lead m-0">
                            Wenn die Reisekette keine ausstehenden Fahrten mehr hat und alle Fahrten erfasst sind, kann die Erfassung beendet werden.
                            Der Button dazu wird dann dargestellt. Es müssen nur noch kurze abschließende Fragen ergänzt werden.
                        </p>
                    </div>
                </div>
                <div class="card mb-2">
                    <div class="card-body">
                        <h2 class="fs-4 fw-bold text-trwl">
                            <i class="fa-solid fa-stopwatch"></i>
                            Was ist, wenn selbstverschuldet etwas schiefgeht?
                        </h2>
                        <p class="lead mb-0">
                            Die Erfassung der geplanten/tatsächlichen Reiseketten dient der Untersuchung der Zuverlässigkeit des öffentlichen Verkehrssystems.
                            Wenn man beispielsweise aus persönlichen Gründen zu spät zur Anfangshaltestelle kommt und damit die geplante Reisekette nicht antreten kann, kann eine eventuelle Verspätung am Zielort nicht mehr in Bezug auf diese geplante Reisekette untersucht werden.
                            Daher muss in so einem Fall also die Reisekette verworfen und eine neue geplant werden.
                            Wenn man eine Reisekette zwischendrin aus Gründen, die nicht dem Verkehrssystem entstammen unterbricht, kann man die (geplante) Reisekette kürzen, deren Erfassung beenden, und für den Rest der Reise eine neue Reisekette anlegen.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
