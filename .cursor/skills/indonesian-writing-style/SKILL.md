---
name: indonesian-writing-style
description: Indonesian documentation and code comment style guide. Use when writing documentation, README files, code comments, or technical explanations in Bahasa Indonesia with formal EYD standards.
---

# Indonesian Documentation Style

Aturan penulisan dokumentasi dalam Bahasa Indonesia formal-profesional.

## Core Principles

- **Bahasa:** Indonesia formal (EYD), tone profesional-informatif
- **Terminologi:** Technical terms in English, explanations in Indonesian
- **Structure:** Systematic, quantifiable, comprehensive

## Key Connectors

| Connector | Fungsi |
|-----------|--------|
| yaitu: | definisi/elaborasi |
| antara lain: | list/contoh |
| dengan demikian, | kesimpulan |
| yang mencakup | detail komponen |
| dimana | kondisi/state |
| serta | info tambahan |
| berdasarkan | referensi data |

## Writing Patterns

### Opening Statement
```
[Subject] merupakan [definisi] yang bertujuan untuk [tujuan], yaitu: [detail]
```

### Bullet Points
```
- [Item] untuk [purpose]
- [Item] yang [detail]
- [Item] dengan [implementasi]
```

### Technical Flow
```
Proses [X] dilakukan melalui tahapan:
1. **[Step]** - [penjelasan]
2. **[Step]** - [penjelasan]
```

### Quantifiable Data
```
[metric] sebesar [angka]% berdasarkan [source]
handling hingga [angka] [unit] dengan [kondisi]
```

## Code Comments

### Function/Method
```javascript
/**
 * Validasi dan normalize user input data
 * dengan sanitization untuk mencegah XSS attack
 *
 * @param {Object} input - Raw input dari user form
 * @returns {Object} Normalized data yang siap disimpan
 */
```

### Inline
```javascript
// Batch processing untuk optimasi performance dimana
// setiap batch berisi maksimal 100 records
const batchSize = 100;
```

## Documentation Structure

```markdown
# [Nama Module]

## Overview
[Module] merupakan [definisi] yang bertujuan untuk [tujuan], yaitu: [detail]

## Prerequisites
Komponen yang diperlukan, antara lain:
- [Item] untuk [purpose]

## Architecture
Sistem mencakup komponen, yaitu:
- **Component** - [fungsi]

## Core Logic
Flow processing melalui tahapan:
1. **Step** - [penjelasan]
```

## Avoid

- Komentar 1-2 kata saja
- Mix English-Indonesia dalam satu kata (salah: "me-migrate", benar: "melakukan migration")
- Skip connector words
- "kita"/"kami" - gunakan passive voice atau "sistem"/"modul"

## Checklist

- [ ] Opening statement jelas
- [ ] Connector words (yaitu, antara lain, dengan demikian)
- [ ] Bullet points deskriptif
- [ ] Terminologi konsisten
- [ ] Quantifiable data included
