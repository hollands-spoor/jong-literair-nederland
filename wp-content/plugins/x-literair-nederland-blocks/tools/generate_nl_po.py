from __future__ import annotations

import re
from pathlib import Path

import polib


def parse_log_mapping(log_path: Path) -> dict[str, str]:
    """Parse NL=>EN lines and return EN=>NL mapping."""
    mapping: dict[str, str] = {}
    line_re = re.compile(r"^\s*-\s*(.*?)\s*=>\s*(.*?)\s*$")

    for line in log_path.read_text(encoding="utf-8").splitlines():
        m = line_re.match(line)
        if not m:
            continue
        nl = m.group(1).strip()
        en = m.group(2).strip()
        if en and nl:
            mapping[en] = nl

    return mapping


def autofill_mapping() -> dict[str, str]:
    """Curated fallback mapping for msgids not present in temp-translations.txt."""
    return {
        "X LN Blocks": "X LN Blocks",
        "Portable collection of LN blocks (or variations). These are used in Literair Nederland and in Jong Literair Nederland.": "Draagbare verzameling LN-blokken (of variaties). Deze worden gebruikt in Literair Nederland en in Jong Literair Nederland.",
        "Hollands Spoor": "Hollands Spoor",
        "Header": "Kop",
        "Sidebar (top)": "Sidebar (boven)",
        "New Ad": "Nieuwe advertentie",
        "Views (unique)": "Weergaven (uniek)",
        "Clicks": "Klikken",
        "Buy now": "Nu kopen",
        "Translation by:": "Vertaling door:",
        "Original title:": "Oorspronkelijke titel:",
        "Foreword by:": "Voorwoord door:",
        "Afterword by:": "Nawoord door:",
        "Illustrations by:": "Illustraties door:",
        "ISBN": "ISBN",
        "ISBN is empty.": "ISBN is leeg.",
        "Failed to fetch bibliographic data.": "Ophalen van bibliografische gegevens mislukt.",
        "Cover image fetched for ISBN %s": "Omslagafbeelding opgehaald voor ISBN %s",
        "Buy with Libris": "Kopen bij Libris",
        "Buy with Bol": "Kopen bij Bol",
        "LN Donation – hello from a dynamic block!": "LN Donatie - hallo vanuit een dynamisch blok!",
        "General": "Algemeen",
        "Dashboard": "Dashboard",
        "Show quick start widget": "Quickstart-widget tonen",
        "Donation": "Donatie",
        "Donation settings": "Donatie-instellingen",
        "Enable Donations": "Donaties inschakelen",
        "Developers": "Ontwikkelaars",
        "Tools": "Tools",
        "Enable tools page": "Pagina Tools inschakelen",
        "Literair Nederland Settings": "Instellingen Literair Nederland",
        "Literair Nederland": "Literair Nederland",
        "Literair Nederland – Quick Start": "Literair Nederland - Snelstart",
        "Welcome to Literair Nederland! Here are some quick links to get you started.": "Welkom bij Literair Nederland! Hier zijn enkele snelle links om te beginnen.",
        "Add interview": "Interview toevoegen",
        "Write a new interview": "Schrijf een nieuw interview",
        "You can hide this widget in the <a href=\"%s\">LN Blocks settings</a>.": "Deze widget kun je verbergen in de <a href=\"%s\">LN Blocks-instellingen</a>.",
        "Literair Nederland Tools": "Tools van Literair Nederland",
        "Insufficient permissions.": "Onvoldoende rechten.",
        "Failed IDs: %s.": "Mislukte ID's: %s.",
        "Image tag has no src attribute.": "Afbeeldingstag heeft geen src-attribuut.",
        "Could not resolve attachment ID for image.": "Kon bijlage-ID voor afbeelding niet bepalen.",
        "Failed to process post content.": "Verwerken van berichtinhoud mislukt.",
        "Jong Literair Nederland": "Jong Literair Nederland",
        "Fallback type": "Fallbacktype",
        "HTML": "HTML",
        "Fallback HTML": "Fallback-HTML",
        "Buy button": "Koopknop",
        "Show buy button": "Koopknop tonen",
        "Design": "Ontwerp",
        "Sticky": "Vastgezet",
        "Change cover image": "Omslagafbeelding wijzigen",
        "Select cover image": "Omslagafbeelding selecteren",
        "Remove cover image": "Omslagafbeelding verwijderen",
        "Fetching bibliographics...": "Bibliografische gegevens ophalen...",
        "Fetch bibliographics": "Bibliografische gegevens ophalen",
        "LN Donation – hello--donation from the editor!": "LN Donatie - hallo--donatie vanuit de editor!",
        "Block with title info.": "Blok met titelinformatie.",
        "LN Donation": "LN Donatie",
        "Donation embed block": "Donatie-embedblok",
    }


def main() -> None:
    root = Path(__file__).resolve().parents[1]
    pot_path = root / "languages" / "x-literair-nederland-blocks.pot"
    po_path = root / "languages" / "x-literair-nederland-blocks-nl_NL.po"
    mo_path = root / "languages" / "x-literair-nederland-blocks-nl_NL.mo"
    log_path = root / "temp-translations.txt"

    if not pot_path.exists():
        raise SystemExit(f"POT not found: {pot_path}")
    if not log_path.exists():
        raise SystemExit(f"Translation log not found: {log_path}")

    en_to_nl = parse_log_mapping(log_path)
    autofill = autofill_mapping()

    po = polib.pofile(str(pot_path))

    # Ensure locale metadata is set for nl_NL.
    po.metadata["Language"] = "nl_NL"
    po.metadata["Content-Type"] = "text/plain; charset=UTF-8"
    po.metadata["Content-Transfer-Encoding"] = "8bit"

    translated_from_log = 0
    translated_from_autofill = 0
    for entry in po:
        if entry.obsolete:
            continue

        if entry.msgid in en_to_nl:
            entry.msgstr = en_to_nl[entry.msgid]
            translated_from_log += 1
            continue

        if not entry.msgstr and entry.msgid in autofill:
            entry.msgstr = autofill[entry.msgid]
            translated_from_autofill += 1

    po.save(str(po_path))
    po.save_as_mofile(str(mo_path))

    total_entries = len([e for e in po if not e.obsolete])
    total_translated = len([e for e in po if not e.obsolete and e.msgstr])
    print(f"Generated PO: {po_path}")
    print(f"Generated MO: {mo_path}")
    print(f"Translated entries from log: {translated_from_log}")
    print(f"Translated entries from autofill: {translated_from_autofill}")
    print(f"Total translated entries: {total_translated}/{total_entries}")


if __name__ == "__main__":
    main()
