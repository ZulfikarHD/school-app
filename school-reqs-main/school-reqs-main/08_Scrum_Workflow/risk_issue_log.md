# Risk & Issue Log

**Purpose:** Track risks dan issues yang muncul selama development, beserta mitigation plan dan resolution.

**Last Updated:** 13 Desember 2025

---

## 📊 Risk Matrix

| ID | Risk | Probability | Impact | Score | Status |
|----|------|-------------|--------|-------|--------|
| R-001 | Scope Creep dari stakeholder | High | High | 9 | Open |
| R-002 | Technical complexity (rapor PDF generation) | Medium | High | 6 | Open |
| R-003 | Stakeholder tidak available untuk review | Medium | Medium | 4 | Open |
| R-004 | WhatsApp API cost tinggi | Low | Medium | 2 | Mitigated |
| R-005 | Data migration dari sistem lama | High | High | 9 | Open |
| R-006 | Server downtime saat go-live | Low | High | 3 | Open |

**Score Calculation:** Probability (Low=1, Medium=2, High=3) × Impact (Low=1, Medium=2, High=3)

---

## Risk Details

### R-001: Scope Creep

**Description:** Stakeholder request fitur baru di tengah sprint yang tidak ada di backlog.

**Probability:** High  
**Impact:** High (delay sprint, burnout)  
**Owner:** Developer (Zulfikar)

**Mitigation Strategy:**
1. Strict DoD - no acceptance tanpa AC terpenuhi
2. Defer semua new requests ke backlog untuk sprint berikutnya
3. Educate stakeholder tentang Scrum process
4. Maintain product backlog yang jelas

**Status:** Open - Need to educate stakeholder di Sprint Planning

**Action Items:**
- [ ] Buat document "How to Request New Feature"
- [ ] Discuss dengan Kepala Sekolah tentang change management

---

### R-002: Technical Complexity (Rapor PDF Generation)

**Description:** Generate PDF rapor dengan format kompleks (tabel, multiple pages, watermark) mungkin lebih sulit dari estimasi.

**Probability:** Medium  
**Impact:** High (delay Sprint 6)  
**Owner:** Developer (Zulfikar)

**Mitigation Strategy:**
1. Spike task di Sprint 5 untuk research PDF libraries (Puppeteer, PDFKit, jsPDF)
2. Simplify template untuk MVP (fancy design bisa fase 2)
3. Re-estimate jika kompleksitas lebih tinggi
4. Buffer time di Sprint 6

**Status:** Open - Will address in Sprint 5

**Action Items:**
- [ ] Sprint 5: Create spike task "Research PDF Generation" (3 hours)
- [ ] Test dengan sample data (10 siswa)
- [ ] Get stakeholder approval untuk template sederhana

---

### R-003: Stakeholder Availability

**Description:** Kepala Sekolah atau TU sering tidak available untuk Sprint Review karena kesibukan.

**Probability:** Medium  
**Impact:** Medium (delayed feedback, potential rework)  
**Owner:** Developer (Zulfikar)

**Mitigation Strategy:**
1. Record video demo untuk async review
2. Schedule Sprint Review 1 minggu sebelumnya
3. Provide staging environment untuk self-test
4. WhatsApp untuk quick feedback

**Status:** Open - Implement from Sprint 1

**Action Items:**
- [ ] Setup screen recording tool (Loom/OBS)
- [ ] Share staging URL dengan credentials
- [ ] Create demo script untuk consistency

---

### R-004: WhatsApp API Cost

**Description:** WhatsApp Business API (Twilio/Fonnte) cost bisa mahal jika volume notifikasi tinggi.

**Probability:** Low  
**Impact:** Medium (budget issue)  
**Owner:** Developer + Stakeholder

**Mitigation Strategy:**
1. Phase 1 MVP: Manual broadcast via WhatsApp Business (gratis)
2. Calculate ROI sebelum invest di API
3. Pilih provider paling cost-effective (Fonnte < Twilio)
4. Batch notifications untuk reduce message count
5. Allow user opt-out untuk non-critical notifications

**Status:** Mitigated - Akan pakai manual broadcast di Phase 1

**Action Items:**
- [x] Confirm dengan stakeholder: manual acceptable untuk MVP
- [ ] Phase 2: Budget proposal untuk auto API

---

### R-005: Data Migration dari Sistem Lama

**Description:** Jika sekolah sudah punya data siswa/pembayaran di Excel/sistem lama, migration bisa risky (data corrupt, format berbeda).

**Probability:** High  
**Impact:** High (delay go-live, data loss)  
**Owner:** Developer (Zulfikar)

**Mitigation Strategy:**
1. Early discovery: Sprint 10 - get sample data dari TU
2. Create import script dengan validation
3. Dry-run import multiple times di staging
4. Backup data lama sebelum migration
5. Parallel run (sistem lama + baru) selama 1-2 minggu

**Status:** Open - Will address in Sprint 10

**Action Items:**
- [ ] Sprint 10: Request sample data (siswa, pembayaran, nilai)
- [ ] Create data mapping document
- [ ] Build import script dengan error handling
- [ ] Test dengan 10-20 sample records
- [ ] Full import di staging
- [ ] Validation checklist

---

### R-006: Server Downtime saat Go-Live

**Description:** Server crash atau performance issue saat pertama kali digunakan banyak user bersamaan.

**Probability:** Low  
**Impact:** High (reputation damage, stress)  
**Owner:** Developer (Zulfikar)

**Mitigation Strategy:**
1. Load testing sebelum go-live (simulate 100-200 users)
2. Monitoring setup: uptime, error tracking (Sentry), server resources
3. Pilih reliable hosting provider (uptime > 99%)
4. Database backup otomatis
5. Rollback plan jika terjadi critical issue
6. Soft launch: pilot dengan 1-2 kelas dulu

**Status:** Open - Will address in Sprint 11-12

**Action Items:**
- [ ] Sprint 11: Setup monitoring (Sentry, UptimeRobot)
- [ ] Sprint 12: Load testing dengan k6 atau Artillery
- [ ] Create runbook untuk common issues
- [ ] Prepare rollback procedure
- [ ] Hotline/WhatsApp untuk urgent support saat launch

---

## 🔥 Issues Log

Issues = problems yang sudah terjadi (bukan risk lagi).

| ID | Issue | Severity | Reported Date | Resolved Date | Status |
|----|-------|----------|---------------|---------------|--------|
| I-001 | Example issue | High | 2025-01-15 | 2025-01-16 | Resolved |

---

### I-001: [Example] Login Page Not Responsive on Mobile

**Description:** Login page layout broken di mobile (iPhone 12 Safari). Button "Masuk" keluar dari screen.

**Severity:** High  
**Reported Date:** 2025-01-15  
**Reported By:** Zulfikar (self-testing)  
**Owner:** Zulfikar

**Impact:**
- User tidak bisa login via mobile
- Blocks Sprint 1 completion

**Root Cause:**
- CSS flexbox tidak dikonfigurasi dengan benar
- Tidak ada media query untuk mobile

**Resolution:**
- Fix CSS dengan mobile-first approach
- Add media query untuk < 768px
- Test di multiple devices (iPhone, Android, tablet)

**Resolved Date:** 2025-01-16

**Lessons Learned:**
- Always test responsive dari awal, jangan tunggu akhir sprint
- Setup browser dev tools device emulation di workflow

**Related Stories:** US-AUTH-001

---

## 📈 Issue Statistics

### By Severity

| Severity | Count | Avg Resolution Time |
|----------|-------|---------------------|
| Critical | 0 | - |
| High | 0 | - |
| Medium | 0 | - |
| Low | 0 | - |

### By Category

| Category | Count |
|----------|-------|
| Frontend Bug | 0 |
| Backend Bug | 0 |
| Database Issue | 0 |
| Performance | 0 |
| Security | 0 |
| UX Issue | 0 |

---

## 🎯 Action Items Summary

### High Priority
- [ ] [Action from R-001]
- [ ] [Action from R-002]

### Medium Priority
- [ ] [Action from R-003]
- [ ] [Action from R-005]

### Low Priority
- [ ] [Action from R-006]

---

## Review Cycle

**Frequency:** Review risks setiap Sprint Planning & Sprint Retrospective  
**Owner:** Developer (Zulfikar)

**Next Review:** [Date of next Sprint Planning]

---

**Maintained By:** Zulfikar Hidayatullah  
**Contact:** +62 857-1583-8733
