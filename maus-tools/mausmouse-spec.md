# .mausmouse format (version 1)

A `.mausmouse` file is a JSON file describing ONE character: a player mouse or a non-player character (a notable mouse, a hireling or a creature). It is made by the Mice & NPCs tool (`mouse-generator.html`, "Save .mausmouse") and can be read by other tools.

Like `.mausmap` and `-player.mausmap`, there are two versions:

- `kind: "pc"`: a player character. Safe to share with its player.
- `kind: "npc"`: a non-player character. The normal file is **GM-only**, because it contains `secret`. "Save player copy" writes a `-player.mausmouse` with `player: true` and no `secret`, for handing a hireling's sheet to the players.

## Shape

```json
{
  "format": "mausmouse",
  "version": 1,
  "kind": "pc",
  "player": false,
  "exported": "2026-10-12T16:30:00.000Z",
  "generator": "mausritter-mice",
  "tables": "mausritter-tables.json",
  "tablesTitle": "Maus Hexcrawl",
  "character": {
    "role": "pc",
    "noun": "mouse",
    "seed": "M.pc.k3j9x1",
    "tables": "mausritter-tables.json",
    "name": "Pepper Thorne",
    "level": 1, "xp": 0, "grit": 0, "pips": 4,
    "str": [9, 9], "dex": [11, 11], "wil": [8, 8], "hp": [4, 4], "armour": 0,
    "background": "Raft crew",
    "birthsign": "Star", "disposition": "Brave / Reckless",
    "coat": "Tan, brindle", "detail": "Whip-like tail",
    "social": "", "pay": "", "appearance": "", "quirk": "", "wants": "", "relationship": "",
    "hireType": "", "wage": "",
    "creature": null,
    "slots": {
      "main": {"name": "Hammer (Medium, d6/d8)", "dots": 3, "used": 1},
      "off": null, "body1": null, "body2": null,
      "pack1": {"name": "Torches", "dots": 3, "used": 0},
      "pack2": {"name": "Rations", "dots": 3, "used": 0},
      "pack3": {"name": "Exhausted", "cond": true, "clear": "After long rest", "effect": "", "dots": 0, "used": 0},
      "pack4": null, "pack5": null, "pack6": null
    },
    "gritSlots": [],
    "extra": [],
    "followers": "",
    "notes": "",
    "secret": ""
  }
}
```

- `role` is `pc`, `notable`, `hireling` or `creature`. `kind` at the top is `pc` for `role: "pc"` and `npc` for everything else.
- `str`, `dex`, `wil` and `hp` are `[max, current]`.
- `slots` holds the inventory. Player mice and notable mice have `main`, `off`, `body1`, `body2` and `pack1`–`pack6`. Hirelings have `main`, `off`, `body1`, `body2`, `pack1` and `pack2`. Creatures have none.
  - Each slot is `null` or an item `{name, dots, used}`. `dots` is the item's usage dots (0–6) and `used` is how many are marked.
  - A condition has `cond: true` plus its `clear` and `effect` text.
  - An item that takes two slots (heavy armour, a two-pawed weapon) appears in its first slot normally and in its second slot with `cont: true`.
- `gritSlots` are conditions placed in the Grit space. `extra` holds items carried beyond the slots, which means the character is encumbered.
- `creature` (creatures only) is `{key, kind, stats, attacks, crit, notes, wants, variant}`. The text is copied from the tables file's `creatures` entry.
- `noun` is what the tables file calls a character (`mouse`, `candyfolk`…). `tables` is the tables file the character was made with.

## What other tools should do

- Check `format` and `version`, read what is useful, ignore unknown fields, and keep unknown fields when saving.
- Never show `secret` to players. A file with `player: true` has none.

## Limits when opening

Text fields are clamped (names and short fields 200 characters; followers, notes and secret 8,000), numbers are kept between -99 and 99,999, at most 10 Grit slots and 50 extra items, file size under 1 MB.
