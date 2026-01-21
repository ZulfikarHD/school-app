# Scrum Workflow - Quick Reference

**For:** Daily development guidance  
**Last Updated:** 13 Desember 2025

---

## 📅 Daily Routine

### Morning (09:00 - 09:15)
- [ ] Review yesterday's progress
- [ ] Check sprint backlog & update task status
- [ ] Identify today's priorities (2-3 tasks max)
- [ ] Check for blockers
- [ ] Write daily standup notes (even for solo developer!)

**Template:**
```
## Daily Standup - [Date]
Yesterday: [What I completed]
Today: [What I'll work on]
Blockers: [None / or describe]
```

---

### During Development (09:15 - 17:00)
- [ ] Work on highest priority task first
- [ ] Time-box: 90 min focus → 10 min break
- [ ] Commit code frequently (small, atomic commits)
- [ ] Update task checklist after each task completion
- [ ] Test immediately after implementing feature (don't wait till end of day)

**Pomodoro-style:**
- 🍅 Session 1: 09:15-10:45 (90 min)
- ☕ Break: 10:45-10:55 (10 min)
- 🍅 Session 2: 10:55-12:25 (90 min)
- 🍴 Lunch: 12:25-13:25 (60 min)
- 🍅 Session 3: 13:25-14:55 (90 min)
- ☕ Break: 14:55-15:05 (10 min)
- 🍅 Session 4: 15:05-16:35 (90 min)
- 📝 Wrap-up: 16:35-17:00 (25 min)

---

### Evening (16:35 - 17:00)
- [ ] Commit & push all work
- [ ] Update sprint backlog (task status, progress %)
- [ ] Write brief summary of today's progress
- [ ] Prepare tomorrow's plan (identify next tasks)
- [ ] Update burndown chart
- [ ] Flag any blockers for tomorrow

---

## ✅ Task Completion Checklist

Before marking task as DONE:

- [ ] Code written & working as expected
- [ ] Linter passed (`yarn run lint`)
- [ ] No console errors/warnings
- [ ] Tested manually (happy path + 1-2 edge cases)
- [ ] Unit tests written (if applicable)
- [ ] Code committed with descriptive message
- [ ] Task checked off in sprint backlog
- [ ] Documentation updated (if needed)

---

## 🎯 Story Completion Checklist (Definition of Done)

Before marking story as DONE:

### Development
- [ ] All tasks completed
- [ ] Code review done (self-review minimal)
- [ ] Linter passed
- [ ] All acceptance criteria met
- [ ] No console errors

### Testing
- [ ] Manual testing (happy path) ✅
- [ ] Edge cases tested
- [ ] Responsive design tested (mobile/tablet/desktop)
- [ ] Browser compatibility (Chrome, Firefox, Safari, Edge)
- [ ] Performance acceptable (< 2 sec load time)

### Documentation
- [ ] Code comments untuk logic kompleks
- [ ] API documentation updated (jika ada perubahan)
- [ ] User guide updated (jika perlu)

### Deployment
- [ ] Deployed ke staging
- [ ] Smoke test di staging passed
- [ ] Ready for demo

---

## 🔄 Sprint Cycle (2-3 weeks)

### Sprint Start (Day 1, Morning)

**Sprint Planning (2-3 jam)**

1. **Review Product Backlog** (30 min)
   - Prioritize top stories
   - Clarify acceptance criteria
   - Check dependencies

2. **Select Sprint Backlog** (60 min)
   - Pick stories berdasarkan priority & velocity
   - Target: 18-20 points untuk 2 weeks
   - Ensure dependencies clear

3. **Define Sprint Goal** (15 min)
   - Write 1-2 kalimat sprint goal
   - Ensure semua stories align dengan goal

4. **Break Down Stories** (45 min)
   - Break down setiap story menjadi tasks
   - Estimate time per task
   - Identify potential blockers

5. **Commit & Document** (15 min)
   - Update sprint backlog document
   - Share dengan stakeholder (if needed)
   - Set calendar reminders (Sprint Review, Retro)

**Output:**
- ✅ Sprint backlog ready
- ✅ Sprint goal defined
- ✅ Tasks broken down & estimated
- ✅ Calendar events created

---

### During Sprint (Day 2 - Day 13)

**Daily Routine:**
- Morning standup (self)
- Development
- Update progress
- Evening wrap-up

**Mid-Sprint Check (Day 7):**
- [ ] Review burndown chart → on track?
- [ ] Any blockers need escalation?
- [ ] Refine backlog jika ada perubahan
- [ ] Send weekly progress report to stakeholder (Friday)

**Weekly Progress Report Template:**
```
Subject: [SD XYZ] Weekly Progress - Sprint X Week Y

Halo Pak/Bu,

=== Progress Minggu Ini ===
✅ Selesai:
- [US-XXX]: [Story] - [Brief description]

🚧 Sedang Dikerjakan:
- [US-YYY]: [Story] - [Progress %]

📅 Rencana Minggu Depan:
- [US-ZZZ]: [Story]

⚠️ Blocker/Isu: [None / or describe]

📊 Sprint Progress: [X] / [Total] points ([%])

Demo: [Link staging]

Regards,
Zulfikar
```

---

### Sprint End (Day 14, Afternoon)

**Sprint Review (1 jam, 14:00)**

1. **Setup** (5 min)
   - Prepare staging environment
   - Test semua features yang akan di-demo
   - Screen recording tool ready (fallback)

2. **Demo** (30 min)
   - Demo completed user stories (show working software!)
   - Follow demo script (prepared beforehand)
   - Get live feedback

3. **Q&A** (15 min)
   - Answer stakeholder questions
   - Note down feedback & change requests

4. **Review Backlog** (10 min)
   - Discuss incomplete stories (if any)
   - Prioritize untuk sprint berikutnya

**Output:**
- ✅ Feedback documented
- ✅ Action items noted
- ✅ Backlog updated

---

**Sprint Retrospective (1 jam, 15:30)**

1. **Data Collection** (10 min)
   - Review sprint metrics (velocity, burndown, time tracking)

2. **What Went Well** (15 min)
   - List positives (min 3 items)

3. **What Didn't Go Well** (15 min)
   - List problems/challenges (be honest!)

4. **What to Improve** (10 min)
   - Brainstorm improvements

5. **Action Items** (10 min)
   - Define 2-3 concrete action items untuk sprint berikutnya
   - Assign owner (self) & deadline

**Output:**
- ✅ Retrospective notes documented
- ✅ 2-3 action items defined

---

## 📊 Metrics to Track

### Daily
- [ ] Time spent per task
- [ ] Tasks completed
- [ ] Blockers encountered

### Sprint
- [ ] Velocity (points completed)
- [ ] Burndown (points remaining per day)
- [ ] Bugs found & fixed
- [ ] Test coverage %

### Release
- [ ] Total stories completed
- [ ] MVP completion %
- [ ] Stakeholder satisfaction score

---

## 🚨 Common Issues & Solutions

### Issue: Story taking longer than estimated

**Solution:**
1. Break down menjadi smaller tasks → identify mana yang bottleneck
2. Time-box: jika sudah 2x estimasi, re-evaluate approach
3. Ask for help: forum, AI, mentor
4. Consider de-scoping: bisa tidak simplify untuk MVP?
5. Update estimation untuk learning

---

### Issue: Blocker (technical/stakeholder unavailable)

**Solution:**
1. Document blocker clearly (what, why, impact)
2. Try workaround (mock data, skip for now, etc.)
3. Notify stakeholder ASAP (WhatsApp/email)
4. Work on other stories (don't wait idle)
5. Set deadline: jika blocker tidak resolve dalam X days, escalate

---

### Issue: Scope creep (stakeholder request new feature mid-sprint)

**Solution:**
1. Listen & note down request
2. Explain: "Noted! Saat ini sedang fokus Sprint X dengan goal [goal]. Bisa saya masukkan request ini ke backlog untuk sprint berikutnya?"
3. Add to product backlog dengan priority
4. Do NOT add to current sprint (kecuali critical bug)

---

### Issue: Feeling burnout

**Solution:**
1. Take a break (seriously!)
2. Review workload: target velocity terlalu tinggi?
3. Adjust next sprint: lower points target (15-17 instead of 20)
4. Focus on high-impact stories (defer nice-to-have)
5. Celebrate small wins

---

## 📝 Git Commit Message Guidelines

Follow conventional commits:

```
<type>(<scope>): <subject>

<body> (optional)

<footer> (optional)
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation only
- `style`: Code style (formatting, no logic change)
- `refactor`: Code refactoring
- `test`: Add/update tests
- `chore`: Maintenance (dependencies, config)

**Examples:**
```
feat(auth): implement login page UI

- Create LoginPage component
- Add form validation
- Integrate with login API

Closes #1
```

```
fix(attendance): fix date picker not working on Safari

The date picker component was using input type="date" which is not
fully supported in Safari. Changed to use react-datepicker library.

Fixes #23
```

```
docs(api): update authentication endpoint documentation

Added response examples and error codes for /api/auth/login
```

---

## 🛠️ Useful Commands

### Development

```bash
# Frontend
cd app
yarn dev                  # Start dev server
yarn build                # Build for production
yarn lint                 # Run linter
yarn test                 # Run tests
yarn test:coverage        # Run tests with coverage

# Backend
cd api
yarn dev                  # Start dev server
yarn build                # Build for production
yarn lint                 # Run linter
yarn test                 # Run tests
yarn migration:run        # Run pending migrations
yarn migration:revert     # Revert last migration

# Database
yarn prisma studio        # Open Prisma Studio (DB GUI)
yarn prisma migrate dev   # Create new migration
```

---

### Git Workflow

```bash
# Start working on story
git checkout -b feature/US-AUTH-001-login
git push -u origin feature/US-AUTH-001-login

# Daily commits
git add .
git commit -m "feat(auth): implement login API endpoint"
git push

# Finish story
git checkout develop
git pull origin develop
git merge feature/US-AUTH-001-login
git push origin develop
git branch -d feature/US-AUTH-001-login  # Delete local branch
```

---

### Deployment (Staging)

```bash
# Deploy ke staging
git checkout staging
git merge develop
git push origin staging

# SSH to server
ssh user@staging.sd-maju.sch.id

# Pull & restart
cd /var/www/sd-maju
git pull origin staging
yarn install
yarn build
pm2 restart sd-maju-api
pm2 restart sd-maju-app
```

---

## 📞 Contact & Resources

### Stakeholders
- **Kepala Sekolah:** Pak [Nama] - [No HP]
- **TU:** Bu [Nama] - [No HP]
- **Emergency Contact:** [No HP]

### Resources
- **Staging:** https://staging.sd-maju.sch.id
- **Database:** [Connection string di .env]
- **API Docs:** https://staging.sd-maju.sch.id/api-docs
- **Figma Design:** [Link]
- **Notion/Trello:** [Link]

### Developer Support
- **Stack Overflow:** https://stackoverflow.com
- **React Docs:** https://react.dev
- **NestJS Docs:** https://docs.nestjs.com
- **ChatGPT/Claude:** For quick questions

---

## 🎯 Sprint Goals Reference

Quick reference sprint goals untuk motivation:

| Sprint | Goal | Key Deliverable |
|--------|------|-----------------|
| Sprint 1 | Foundation | Auth & RBAC working |
| Sprint 2 | Core Data | Student Management CRUD |
| Sprint 3 | Enhancement | Student data complete + Attendance start |
| Sprint 4 | Attendance | Attendance system functional |
| Sprint 5 | Payment | Payment system functional |
| Sprint 6 | Grades | Grades input & rapor PDF |
| Sprint 7 | Grades Portal | Orang tua dapat lihat rapor online |
| Sprint 8 | PSB & Teacher | PSB workflow + Teacher management |
| Sprint 9 | Dashboard | Dashboard untuk semua role |
| Sprint 10 | Reports | Laporan keuangan & akademik |
| Sprint 11 | Website | School website live |
| Sprint 12 | Polish | Notification, config, final polish |

---

## 💡 Pro Tips

1. **Time-box everything:** Jangan stuck terlalu lama di 1 task. If > 2x estimasi, re-evaluate.

2. **Test early, test often:** Jangan tunggu akhir sprint untuk testing. Test setiap selesai 1 feature.

3. **Commit often:** Small commits > big commits. Easier to revert jika ada masalah.

4. **Document as you go:** Jangan tunggu akhir sprint untuk dokumentasi.

5. **Celebrate small wins:** Setiap story completed adalah achievement! 🎉

6. **Health first:** Burnout = no productivity. Take breaks, exercise, sleep enough.

7. **Ask for help:** Jangan malu tanya (forum, AI, mentor). Better ask early than stuck for days.

8. **Focus on value:** Prioritize features yang paling dibutuhkan stakeholder. Perfect is the enemy of done.

9. **Automate repetitive tasks:** Linter, tests, deployment → automate!

10. **Reflect & improve:** Every sprint retrospective → learn & improve.

---

## 🎉 Motivation

> "The secret to getting ahead is getting started." - Mark Twain

> "Progress, not perfection."

> "Done is better than perfect."

**You got this! 💪**

---

**Maintained By:** Zulfikar Hidayatullah  
**Last Updated:** 13 Desember 2025

**Questions?** WhatsApp: +62 857-1583-8733
