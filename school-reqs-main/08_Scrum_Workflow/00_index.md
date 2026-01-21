# Scrum Workflow Documentation - Index

**Project:** Sistem Manajemen Sekolah SD  
**Developer:** Zulfikar Hidayatullah (+62 857-1583-8733)  
**Version:** 1.0  
**Last Updated:** 13 Desember 2025

---

## 📚 Documentation Structure

Dokumen ini berisi semua yang Anda butuhkan untuk menjalankan Scrum workflow dengan efektif.

---

## 📖 Core Documents

### 1. [readme.md](readme.md) - **START HERE!**
**Main Scrum Workflow Document**

Baca dokumen ini terlebih dahulu untuk memahami keseluruhan workflow.

**Contents:**
- 📊 Product Backlog Overview (122 stories, 273 points)
- 🎯 11 Epic Organization (terorganisir berdasarkan value stream)
- 🏃 12 Sprint Planning (detail breakdown per sprint)
- 📅 Release Plan (MVP target: 6-7 bulan)
- ✅ Definition of Done
- 🔗 Dependencies Matrix
- ⚠️ Risk Management
- 📈 Success Metrics

**When to read:** Sebelum mulai Sprint 1, atau ketika butuh big picture view.

---

### 2. [quick_reference.md](quick_reference.md) - **DAILY USE**
**Quick Reference Guide untuk Development Sehari-hari**

Gunakan dokumen ini setiap hari sebagai checklist dan panduan cepat.

**Contents:**
- 📅 Daily Routine (morning, during dev, evening)
- ✅ Task & Story Completion Checklist
- 🔄 Sprint Cycle overview
- 🚨 Common Issues & Solutions
- 📝 Git Commit Guidelines
- 🛠️ Useful Commands
- 💡 Pro Tips

**When to read:** Daily! Bookmark this file.

---

## 📝 Templates & Examples

### 3. [sprint_backlog_template.md](sprint_backlog_template.md)
**Template untuk Setiap Sprint**

Copy template ini untuk setiap sprint baru (Sprint 1, Sprint 2, dst).

**Contents:**
- Sprint Information (number, goal, duration, points)
- User Stories list
- Task Breakdown per story
- Daily Progress Tracking
- Sprint Metrics (burndown chart)
- Sprint Review & Retrospective notes

**When to use:** Di awal setiap sprint (Sprint Planning).

---

### 4. [sprint_01_example.md](sprint_01_example.md)
**Contoh Lengkap Sprint 1 (Fully Planned)**

Contoh konkret bagaimana Sprint 1 di-breakdown secara detail.

**Contents:**
- Sprint 1 Goal & Success Criteria
- 7 User Stories dengan task breakdown lengkap
- Time estimation per task (Frontend, Backend, Testing)
- Daily progress tracking example
- Sprint review & retrospective example
- Metrics & learnings

**When to read:** 
- Sebelum mulai Sprint 1 (sebagai reference)
- Ketika butuh contoh konkret task breakdown

---

### 5. [story_task_breakdown_guide.md](story_task_breakdown_guide.md)
**Panduan Breakdown User Stories ke Tasks**

Panduan step-by-step untuk memecah user story menjadi actionable tasks.

**Contents:**
- Why break down stories?
- Task breakdown template
- 2 Detailed examples (US-AUTH-001, US-STD-001)
- Task estimation guidelines (time per task type)
- Task checklist (DoD per task)
- DO's and DON'Ts

**When to read:**
- Sprint Planning (saat breakdown stories)
- Ketika stuck bagaimana breakdown story yang kompleks

---

## 🎯 Tracking & Management

### 6. [risk_issue_log.md](risk_issue_log.md)
**Risk & Issue Tracking Log**

Dokumentasi semua risks dan issues yang muncul, beserta mitigation plan.

**Contents:**
- Risk Matrix (6 identified risks dengan score)
- Detailed risk analysis & mitigation strategy
- Issues Log (problems yang sudah terjadi)
- Action Items Summary

**When to use:**
- Sprint Planning (review risks)
- Sprint Retrospective (update risks, add issues)
- Weekly: check jika ada new risks/issues

**Current Risks:**
- R-001: Scope Creep (High)
- R-002: Technical Complexity (Medium)
- R-005: Data Migration (High)

---

## 🗺️ Navigation Guide

### Untuk Solo Developer (Zulfikar)

**1. Pre-Sprint (Sebelum mulai project):**
- [ ] Read: [readme.md](readme.md) - full Scrum Workflow
- [ ] Read: [quick_reference.md](quick_reference.md) - daily routine
- [ ] Setup: Project structure, tools, environments

**2. Sprint Planning (Awal setiap sprint):**
- [ ] Copy: [sprint_backlog_template.md](sprint_backlog_template.md) → `sprint_0X_backlog.md`
- [ ] Reference: [sprint_01_example.md](sprint_01_example.md) untuk contoh breakdown
- [ ] Use: [story_task_breakdown_guide.md](story_task_breakdown_guide.md) untuk breakdown stories
- [ ] Review: [risk_issue_log.md](risk_issue_log.md) untuk risks
- [ ] Fill: Sprint backlog dengan selected stories & tasks
- [ ] Commit: Sprint goal & inform stakeholder

**3. Daily Development:**
- [ ] Open: [quick_reference.md](quick_reference.md) setiap pagi
- [ ] Follow: Daily routine (standup, development, wrap-up)
- [ ] Update: Sprint backlog (task status, progress)
- [ ] Commit: Code frequently dengan good commit messages
- [ ] Check: Task completion checklist sebelum mark DONE

**4. Mid-Sprint (Week 2):**
- [ ] Review: Burndown chart → on track?
- [ ] Send: Weekly progress report to stakeholder
- [ ] Update: [risk_issue_log.md](risk_issue_log.md) jika ada issues

**5. Sprint Review (Akhir sprint):**
- [ ] Prepare: Demo environment & script
- [ ] Demo: Working software to stakeholder
- [ ] Collect: Feedback & action items
- [ ] Update: Sprint backlog (review section)

**6. Sprint Retrospective (Setelah review):**
- [ ] Reflect: What went well, what didn't, what to improve
- [ ] Define: 2-3 action items untuk sprint berikutnya
- [ ] Update: Sprint backlog (retrospective section)
- [ ] Update: [risk_issue_log.md](risk_issue_log.md) dengan learnings

**7. Repeat untuk Sprint berikutnya! 🔄**

---

### Untuk Stakeholder (Kepala Sekolah, TU)

**1. Awal Project:**
- [ ] Read: [readme.md](readme.md) - Section "Epic Organization" & "Sprint Planning"
- [ ] Understand: Sprint cycle, release plan, timeline
- [ ] Discuss: Priorities, MVP scope, phase 2 plans

**2. Every Sprint:**
- [ ] Receive: Weekly progress report (Friday)
- [ ] Attend: Sprint Review (setiap 2-3 minggu)
- [ ] Provide: Feedback & clarifications
- [ ] Review: Staging environment (test features)

**3. Anytime:**
- [ ] Request: New features → add to product backlog (tidak langsung ke sprint)
- [ ] Report: Bugs → create issue
- [ ] Ask: Questions via WhatsApp/email

---

## 📊 Quick Stats

### Project Overview

| Metric | Value |
|--------|-------|
| **Total User Stories** | 122 stories |
| **Total Estimation** | 273 points |
| **Phase 1 (MVP)** | 215 points (88 stories) |
| **Phase 2 (Enhancement)** | 58 points (34 stories) |
| **Estimated Timeline** | 6-8 bulan (MVP) |
| **Total Sprints (MVP)** | 12 sprints (2-3 weeks each) |
| **Target Velocity** | 18-20 points/sprint |

---

### Epic Overview

| Epic | Points | Status |
|------|--------|--------|
| Epic 1: Foundation & Access Control | 40 | Sprint 1-2 |
| Epic 2: Student Information System | 26 | Sprint 2-3 |
| Epic 3: Daily Attendance Management | 31 | Sprint 3-5 |
| Epic 4: Financial Management | 29 | Sprint 4-6 |
| Epic 5: Academic Performance Tracking | 28 | Sprint 6-8 |
| Epic 6: Student Recruitment (PSB) | 24 | Sprint 7-8 |
| Epic 7: Teacher Management & HR | 24 | Sprint 8-9 |
| Epic 8: Executive Dashboard & Reporting | 35 | Sprint 9-11 |
| Epic 9: Public Website & Communication | 27 | Sprint 10-11 |
| Epic 10: Notification & Communication | 22 | Sprint 11-12 |
| Epic 11: System Config & Administration | 14 | Sprint 12 |

---

## 🔍 Search & Find

**Need to find something specific?**

### By Topic

- **Authentication:** Epic 1, Sprint 1-2
- **Student Data:** Epic 2, Sprint 2-3
- **Attendance:** Epic 3, Sprint 3-5
- **Payment:** Epic 4, Sprint 4-6
- **Grades/Rapor:** Epic 5, Sprint 6-8
- **PSB (Registration):** Epic 6, Sprint 7-8
- **Teacher Management:** Epic 7, Sprint 8-9
- **Dashboard:** Epic 8, Sprint 9-11
- **Website:** Epic 9, Sprint 10-11
- **Notification:** Epic 10, Sprint 11-12
- **Settings:** Epic 11, Sprint 12

### By Sprint

- **Sprint 1:** Foundation, Auth, RBAC, Master Data → [sprint_01_example.md](sprint_01_example.md)
- **Sprint 2:** Student Management, Password Reset
- **Sprint 3:** Student Enhancement, Attendance Start
- **Sprint 4:** Attendance Complete, Payment Start
- **Sprint 5:** Payment Complete
- **Sprint 6:** Grades Foundation, Rapor PDF
- **Sprint 7:** Grades Portal, PSB Start
- **Sprint 8:** PSB Complete, Teacher Management
- **Sprint 9:** Teacher Complete, Dashboard Start
- **Sprint 10:** Dashboard & Reports
- **Sprint 11:** Website & Notification
- **Sprint 12:** Final Polish, Config, UAT

### By Document Type

- **Overview/Planning:** [readme.md](readme.md)
- **Daily Use:** [quick_reference.md](quick_reference.md)
- **Templates:** [sprint_backlog_template.md](sprint_backlog_template.md)
- **Examples:** [sprint_01_example.md](sprint_01_example.md)
- **Guides:** [story_task_breakdown_guide.md](story_task_breakdown_guide.md)
- **Tracking:** [risk_issue_log.md](risk_issue_log.md)

---

## 🎯 Milestones

### Key Milestones untuk MVP

| Milestone | Target Sprint | Description |
|-----------|---------------|-------------|
| **M1: Foundation Ready** | End of Sprint 2 | Auth, RBAC, Master Data, Student CRUD done |
| **M2: Core Operations** | End of Sprint 5 | Attendance & Payment system fully functional |
| **M3: Academic Complete** | End of Sprint 8 | Grades, Rapor, PSB, Teacher Management done |
| **M4: Monitoring Ready** | End of Sprint 11 | Dashboard, Reports, Website, Notification done |
| **M5: MVP Complete** | End of Sprint 12 | All Must Have & Should Have features done, UAT ready |
| **M6: Go-Live** | 2-4 weeks after M5 | Production deployment, training, data migration complete |

---

## 📅 Important Dates (Example Timeline)

**Assuming Start Date: 2 Januari 2025**

| Event | Date | Description |
|-------|------|-------------|
| Project Kickoff | 2 Jan 2025 | Sprint Planning Sprint 1 |
| Sprint 1 Review | 15 Jan 2025 | Demo: Auth & Foundation |
| Sprint 2 Review | 29 Jan 2025 | Demo: Student Management |
| Sprint 4 Review | 26 Feb 2025 | Demo: Attendance Complete |
| Sprint 6 Review | 25 Mar 2025 | Demo: Payment & Grades |
| Sprint 8 Review | 22 Apr 2025 | Demo: PSB & Teacher |
| Sprint 10 Review | 20 Mei 2025 | Demo: Dashboard & Reports |
| Sprint 12 Review | 17 Jun 2025 | **MVP Demo - UAT Start** |
| Go-Live Target | Jul 2025 | Production Deployment |

*(Adjust dates berdasarkan actual start date)*

---

## 💡 Tips for Success

1. **Read readme.md first** untuk big picture understanding
2. **Bookmark quick_reference.md** untuk daily use
3. **Follow sprint template** untuk consistency
4. **Update documents regularly** (sprint backlog, risk log)
5. **Communicate with stakeholder** (weekly report, sprint review)
6. **Celebrate small wins** - every sprint completed is progress! 🎉

---

## 📞 Support & Contact

**Developer:** Zulfikar Hidayatullah  
**Phone/WhatsApp:** +62 857-1583-8733  
**Email:** [Email jika ada]

**Stakeholder:**
- Kepala Sekolah: [Nama & Kontak]
- TU: [Nama & Kontak]

---

## 📚 External Resources

- **User Stories Full Detail:** `../04_User_Stories_With_Acceptance_Criteria/`
- **Functional Requirements:** `../02_Functional_Requirements/`
- **System Architecture:** `../05_System_Architecture_Recommendation/`
- **Technical Considerations:** `../07_Technical_Considerations/`

---

## ✅ Quick Checklist - Am I Ready?

### Before Starting Sprint 1:

- [ ] Read [readme.md](readme.md) completely
- [ ] Read [quick_reference.md](quick_reference.md)
- [ ] Read [sprint_01_example.md](sprint_01_example.md)
- [ ] Setup development environment (Node.js, database, IDE, Git)
- [ ] Create project repositories (frontend, backend)
- [ ] Setup communication channel dengan stakeholder (WhatsApp group, email)
- [ ] Calendar: block time untuk Sprint Review & Retro (setiap 2-3 minggu)

### During Sprint:

- [ ] Daily standup (self) - morning routine
- [ ] Update sprint backlog daily
- [ ] Commit code frequently
- [ ] Test features immediately
- [ ] Check [quick_reference.md](quick_reference.md) for guidance

### End of Sprint:

- [ ] All stories meet Definition of Done
- [ ] Sprint Review prepared (demo script, staging ready)
- [ ] Collect stakeholder feedback
- [ ] Sprint Retrospective (reflect & improve)
- [ ] Update [risk_issue_log.md](risk_issue_log.md)

---

## 🚀 Let's Build This!

**Good luck dengan project development! 💪**

Jika ada pertanyaan atau butuh klarifikasi tentang workflow, jangan ragu untuk reach out.

---

**Version:** 1.0  
**Last Updated:** 13 Desember 2025  
**Status:** Ready to Start Sprint 1 🚀
