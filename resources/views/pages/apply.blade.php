<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <link rel="icon" type="image/png" href="images/gocare-institute-logo.png">
  <link rel="apple-touch-icon" href="images/gocare-institute-logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Online Application &middot; GoCare Training Institute</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    :root { --o: #ec7424; --p: #642a7e; --w: #fcf9f8; --dark: #4a1a6d; }
    body { background: var(--w); font-family: 'Inter', sans-serif; color: #374151; overflow-x: hidden; }

    /* -- FLOATING BACKGROUND ICONS (animated watermark) -- */
    .bg-float-wrap { position: fixed; inset: 0; pointer-events: none; overflow: hidden; z-index: 0; }
    .bg-float { position: absolute; opacity: 0.055; }
    .bg-float--school { width: 320px; bottom: 8%; left: -40px; animation: floatUD 7s ease-in-out infinite; }
    .bg-float--grad { width: 280px; top: 16%; right: -30px; animation: floatUD 7s ease-in-out infinite 3.5s; }
    @keyframes floatUD { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-28px); } }
    @media (max-width: 768px) {
      .bg-float--school { width: 170px; left: -50px; }
      .bg-float--grad { width: 150px; right: -50px; }
    }

    /* -- PAGE LAYOUT (transparent so watermark shows through) -- */
    .app-page { background: transparent; min-height: 80vh; padding: 0 0 80px; position: relative; z-index: 1; }
    .app-wrap { max-width: 1100px; margin: 50px auto 0; padding: 0 20px; position: relative; z-index: 1; }

    /* -- INTRO WELCOME SCREEN -- */
    .form-intro { background: #fff; border: 1px solid #e5e7eb; border-radius: 20px; padding: 48px 52px; margin: 0 auto 40px; max-width: 860px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .form-intro h1 { font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 800; color: #111; margin-bottom: 20px; }
    .form-intro .intro-lead { font-size: 1.02rem; color: #111; margin-bottom: 18px; }
    .form-intro p { font-size: 0.97rem; color: #374151; line-height: 1.75; margin-bottom: 14px; }
    .intro-begin-btn { margin-top: 28px; display: inline-flex; align-items: center; gap: 8px; background: var(--o); color: #fff; font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1rem; padding: 14px 32px; border: none; border-radius: 50px; cursor: pointer; transition: background .2s, transform .2s; box-shadow: 0 6px 20px rgba(236,116,36,.3); }
    .intro-begin-btn:hover { background: #d05d15; transform: translateY(-2px); }
    .intro-begin-btn i { width: 18px; height: 18px; }

    /* -- STEPPER -- */
    .stepper { display: flex; align-items: flex-start; justify-content: center; gap: 0; margin-bottom: 50px; flex-wrap: wrap; }
    .step-item { display: flex; align-items: center; }
    .step-circle { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; transition: 0.3s; flex-shrink: 0; }
    .step-circle.active { background: var(--o); color: #fff; box-shadow: 0 4px 15px rgba(236,116,36,0.4); }
    .step-circle.done { background: var(--dark); color: #fff; }
    .step-circle.pending { background: #e2e8f0; color: #94a3b8; }
    .step-label { font-size: 0.8rem; font-weight: 600; margin-top: 8px; text-align: center; max-width: 90px; line-height: 1.3; }
    .step-label.active-label { color: var(--o); }
    .step-label.done-label { color: var(--dark); }
    .step-label.pending-label { color: #94a3b8; }
    .step-col { display: flex; flex-direction: column; align-items: center; }
    .step-line { width: 56px; height: 3px; border-radius: 2px; margin: 0 5px; margin-top: 22px; transition: .3s; }
    .step-line.done { background: var(--dark); }
    .step-line.active { background: linear-gradient(90deg, var(--dark), var(--o)); }
    .step-line.pending { background: #e2e8f0; }

    /* -- GRID -- */
    .app-grid { display: grid; grid-template-columns: 1fr 340px; gap: 30px; align-items: start; }
    .form-step { display: none; animation: stepFade 0.45s ease forwards; }
    .form-step.active { display: block; }
    @keyframes stepFade { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

    .app-form-card { background: #fff; border-radius: 16px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid rgba(0,0,0,0.04); }
    .app-form-card h2 { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.6rem; font-weight: 700; color: var(--dark); margin-bottom: 30px; padding-bottom: 15px; border-bottom: 2px solid var(--o); display: flex; align-items: center; gap: 10px; }

    /* ── CREATIVE FORM FIELDS ─────────────────────────────── */
    .form-group { margin-bottom: 22px; position: relative; }
    .form-group > label { display: flex; align-items: center; gap: 6px; font-size: 0.82rem; font-weight: 700; letter-spacing: .2px; text-transform: uppercase; color: var(--dark); margin-bottom: 9px; }

    .form-group input, .form-group select, .form-group textarea {
      width: 100%; padding: 14px 16px; font-size: 0.97rem; font-family: inherit; color: #1e293b;
      border: 1.6px solid #e6e8ef; border-radius: 12px; outline: none;
      background: #f8fafc; box-shadow: inset 0 1px 2px rgba(15,23,42,0.03);
      transition: border-color .22s ease, box-shadow .22s ease, background .22s ease, transform .22s ease;
      -webkit-appearance: none; appearance: none;
    }
    .form-group input::placeholder, .form-group textarea::placeholder { color: #9aa5b6; font-weight: 400; }
    .form-group input:hover, .form-group select:hover, .form-group textarea:hover { border-color: #cbd3e1; background: #fff; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
      border-color: var(--o); background: #fff;
      box-shadow: 0 0 0 4px rgba(236,116,36,0.14), 0 6px 16px rgba(236,116,36,0.10);
    }
    /* filled fields get a gentle "completed" tint */
    .form-group input:not(:placeholder-shown):not(.input-error),
    .form-group textarea:not(:placeholder-shown):not(.input-error) { border-color: #cdd5e3; background: #fff; }

    /* custom dropdown chevron */
    .form-group select {
      padding-right: 44px; cursor: pointer; color: #1e293b;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%23642a7e' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
      background-repeat: no-repeat; background-position: right 15px center;
    }
    .form-group select:focus { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%23ec7424' stroke-width='2.6' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); }
    .form-group select option { color: #1e293b; }

    /* textarea */
    .form-group textarea { min-height: 118px; line-height: 1.6; resize: vertical; }

    /* date picker indicator */
    .form-group input[type="date"] { color: #1e293b; }
    .form-group input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; opacity: .55; transition: opacity .2s; }
    .form-group input[type="date"]:hover::-webkit-calendar-picker-indicator { opacity: 1; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

    /* phone prefix — merged control with shared focus ring */
    .phone-prefix { display: flex; border-radius: 12px; transition: box-shadow .22s ease; }
    .phone-prefix span {
      display: inline-flex; align-items: center; gap: 6px; padding: 14px 16px; font-weight: 700; color: var(--dark);
      background: linear-gradient(135deg, rgba(236,116,36,0.12), rgba(236,116,36,0.06));
      border: 1.6px solid #e6e8ef; border-right: none; border-radius: 12px 0 0 12px; white-space: nowrap;
    }
    .phone-prefix input { border-radius: 0 12px 12px 0; box-shadow: none; }
    .phone-prefix:focus-within span { border-color: var(--o); color: var(--o); background: rgba(236,116,36,0.14); }
    .phone-prefix:focus-within { box-shadow: 0 0 0 4px rgba(236,116,36,0.14); }
    .phone-prefix:focus-within input { box-shadow: none; }

    /* radio group as segmented "chip" controls */
    .radio-group { display: flex; gap: 12px; padding: 2px 0; flex-wrap: wrap; }
    .radio-group label {
      display: inline-flex; align-items: center; gap: 9px; cursor: pointer; font-weight: 600; font-size: 0.92rem;
      color: #64748b; white-space: nowrap; padding: 11px 18px; border: 1.6px solid #e6e8ef; border-radius: 50px;
      background: #f8fafc; transition: all .2s ease; user-select: none;
    }
    .radio-group label::before {
      content: ''; width: 17px; height: 17px; border-radius: 50%; border: 2px solid #cbd3e1;
      background: #fff; flex-shrink: 0; transition: all .2s ease; box-shadow: inset 0 0 0 4px #fff;
    }
    .radio-group input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; margin: 0; }
    .radio-group label:hover { border-color: rgba(236,116,36,0.55); color: #475569; }
    .radio-group label:has(input:checked) {
      border-color: var(--o); background: rgba(236,116,36,0.08); color: var(--o);
      box-shadow: 0 4px 14px rgba(236,116,36,0.15);
    }
    .radio-group label:has(input:checked)::before { border-color: var(--o); background: var(--o); box-shadow: inset 0 0 0 3px #fff; }
    .radio-group label:has(input:focus-visible) { outline: 2px solid rgba(236,116,36,0.5); outline-offset: 2px; }

    .btn-next { background: linear-gradient(135deg, var(--o), #d05d15); color: #fff; border: none; padding: 14px 40px; border-radius: 10px; font-weight: 700; font-size: 1rem; cursor: pointer; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap; }
    .btn-next:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(236,116,36,0.4); }
    .btn-prev { background: transparent; color: var(--dark); border: 2px solid var(--dark); padding: 14px 30px; border-radius: 10px; font-weight: 700; font-size: 1rem; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
    .btn-prev:hover { background: var(--dark); color: #fff; }
    .btn-submit { background: linear-gradient(135deg, var(--dark), var(--p)); color: #fff; border: none; padding: 16px 50px; border-radius: 12px; font-weight: 700; font-size: 1.1rem; cursor: pointer; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px; display: inline-flex; align-items: center; gap: 10px; }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(74,26,109,0.4); }
    .form-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 30px; padding-top: 20px; border-top: 2px solid #f1f5f9; gap: 12px; }

    /* upload zones */
    .upload-zone { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 30px; text-align: center; cursor: pointer; transition: 0.3s; background: #f8fafc; }
    .upload-zone:hover { border-color: var(--o); background: rgba(236,116,36,0.03); }
    .upload-zone i { color: var(--o); margin-bottom: 10px; }
    .upload-zone p { color: #64748b; font-size: 0.9rem; }
    .upload-zone .upload-label { font-weight: 600; color: var(--dark); font-size: 1rem; margin-bottom: 5px; }
    .upload-zone.dragover { border-color: var(--o); background: rgba(236,116,36,0.08); }
    .upload-zone.has-file { border-style: solid; border-color: #16a34a; background: rgba(22,163,74,0.04); }
    .upload-zone.has-file i { color: #16a34a; }
    .upload-zone.upload-error { border-color: #dc2626; background: rgba(220,38,38,0.04); }
    .upload-zone.upload-error i { color: #dc2626; }
    .upload-zone .upload-filename { font-weight: 600; color: #16a34a; font-size: 0.9rem; word-break: break-word; }
    .upload-zone .upload-error-msg { color: #dc2626; font-size: 0.85rem; font-weight: 600; }

    /* payment gate — locked until M-Pesa confirms the fee */
    .pay-gate { display: flex; align-items: flex-start; gap: 10px; margin: 18px 0 0; padding: 13px 16px;
      border-radius: 10px; font-size: .9rem; line-height: 1.55; font-weight: 600;
      background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; }
    .pay-gate .pay-gate-icon { width: 18px; height: 18px; flex-shrink: 0; margin-top: 2px; }
    .pay-gate--waiting { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }
    .pay-gate--paid { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }

    /* resume-a-saved-application prompt on the intro screen */
    .resume-box { display: none; margin: 0 0 26px; padding: 18px 20px; border-radius: 12px;
      background: #fff7ed; border: 1px solid #fed7aa; text-align: left; }
    .resume-box h4 { display: flex; align-items: center; gap: 8px; margin-bottom: 6px;
      font-family: 'Outfit', sans-serif; font-size: 1rem; color: #9a3412; }
    .resume-box p { font-size: .9rem; color: #7c2d12; margin-bottom: 14px; line-height: 1.6; }
    .resume-box .resume-actions { display: flex; flex-wrap: wrap; gap: 10px; }
    .resume-box button { border: none; border-radius: 8px; padding: 11px 20px; font-weight: 700;
      font-size: .9rem; cursor: pointer; font-family: inherit; }
    .resume-btn-primary { background: var(--o); color: #fff; }
    .resume-btn-ghost { background: transparent; color: #9a3412; border: 1px solid #fed7aa !important; }
    .autosave-note { font-size: .8rem; color: #94a3b8; margin-top: 10px; display: flex;
      align-items: center; gap: 6px; }

    /* payment */
    .pay-methods { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
    .pay-card { border: 2px solid #e2e8f0; border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: 0.3s; }
    .pay-card:hover { border-color: var(--o); background: rgba(236,116,36,0.03); }
    .pay-card.selected { border-color: var(--o); background: rgba(236,116,36,0.06); box-shadow: 0 4px 12px rgba(236,116,36,0.15); }
    .pay-card i { color: var(--o); margin-bottom: 8px; }
    .pay-card h4 { font-size: 0.95rem; font-weight: 700; color: var(--dark); margin-bottom: 4px; }
    .pay-card p { font-size: 0.8rem; color: #64748b; }
    .fee-summary { background: linear-gradient(135deg, rgba(74,26,109,0.05), rgba(100,42,126,0.03)); border-radius: 12px; padding: 20px; margin-bottom: 24px; border: 1px solid rgba(74,26,109,0.1); }
    .fee-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 0.9rem; color: #334155; }
    .fee-row.total { border-top: 2px solid var(--dark); padding-top: 12px; margin-top: 8px; font-weight: 700; font-size: 1.1rem; color: var(--dark); }
    .payment-info-box { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 10px; padding: 16px; margin-bottom: 20px; }
    .payment-info-box h4 { display: flex; align-items: center; gap: 8px; color: var(--o); margin-bottom: 8px; font-size: 0.95rem; }
    .payment-info-box p { font-size: 0.85rem; color: #475569; line-height: 1.6; }

    /* review */
    .review-section { margin-bottom: 24px; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
    .review-header { display: flex; justify-content: space-between; align-items: center; background: #f8fafc; padding: 14px 20px; }
    .review-header h3 { font-size: 1rem; font-weight: 700; color: var(--dark); display: flex; align-items: center; gap: 8px; }
    .review-header h3 i { color: var(--o); }
    .review-header button { font-size: 0.85rem; color: var(--o); font-weight: 600; background: none; border: none; cursor: pointer; }
    .review-header button:hover { text-decoration: underline; }
    .review-body { padding: 20px; }
    .review-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f5f9; gap: 16px; }
    .review-row:last-child { border-bottom: none; }
    .review-label { font-size: 0.85rem; color: #64748b; font-weight: 500; }
    .review-value { font-size: 0.9rem; color: #1e293b; font-weight: 600; text-align: right; }
    .checkbox-agree { display: flex; align-items: flex-start; gap: 12px; padding: 20px; background: rgba(74,26,109,0.03); border-radius: 12px; margin-top: 24px; border: 1px solid rgba(74,26,109,0.1); }
    .checkbox-agree input[type="checkbox"] { width: 20px; height: 20px; margin-top: 2px; accent-color: var(--o); flex-shrink: 0; }
    .checkbox-agree label { font-size: 0.9rem; color: #334155; line-height: 1.6; }
    .checkbox-agree label a { color: var(--o); text-decoration: underline; }

    /* sidebar */
    .app-sidebar { display: flex; flex-direction: column; gap: 24px; position: sticky; top: 100px; }
    .sidebar-card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid rgba(0,0,0,0.04); }
    .sidebar-card img { width: 100%; height: 180px; object-fit: cover; }
    .sidebar-card .sidebar-body { padding: 24px; }
    .sidebar-card h3 { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.2rem; font-weight: 700; color: var(--dark); margin-bottom: 10px; }
    .sidebar-card p { font-size: 0.9rem; color: #475569; line-height: 1.6; margin-bottom: 15px; }
    .sidebar-card ul { list-style: none; padding: 0; margin: 0; }
    .sidebar-card ul li { display: flex; align-items: flex-start; gap: 10px; padding: 10px 0; border-top: 1px solid #f1f5f9; font-size: 0.9rem; color: #334155; line-height: 1.5; }
    .sidebar-card ul li i { color: var(--o); flex-shrink: 0; margin-top: 3px; }
    .sidebar-tips { background: linear-gradient(135deg, rgba(236,116,36,0.08), rgba(236,116,36,0.03)); border-radius: 16px; padding: 24px; border: 1px solid rgba(236,116,36,0.15); }
    .sidebar-tips h3 { display: flex; align-items: center; gap: 10px; font-size: 1.1rem; font-weight: 700; color: var(--o); margin-bottom: 16px; }
    .sidebar-tips ul { list-style: none; padding: 0; margin: 0; }
    .sidebar-tips ul li { display: flex; gap: 10px; padding: 8px 0; font-size: 0.85rem; color: #475569; line-height: 1.5; }
    .sidebar-tips ul li i { color: var(--o); flex-shrink: 0; margin-top: 2px; }

    /* success overlay */
    .success-overlay { position: fixed; inset: 0; background: rgba(74,26,109,0.85); display: none; align-items: center; justify-content: center; z-index: 9999; padding: 20px; }
    .success-overlay.show { display: flex; }
    .success-modal { background: #fff; border-radius: 20px; padding: 50px 40px; text-align: center; max-width: 500px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
    .success-icon { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--o), #d05d15); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; }
    .success-icon i { color: #fff; width: 40px; height: 40px; }
    .success-modal h2 { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.8rem; font-weight: 800; color: var(--dark); margin-bottom: 10px; }
    .success-modal p { font-size: 1rem; color: #475569; margin-bottom: 8px; line-height: 1.6; }
    .success-modal .app-id { display: inline-block; background: #f1f5f9; padding: 8px 16px; border-radius: 8px; font-weight: 700; color: var(--dark); margin: 16px 0 24px; font-size: 1.1rem; }

    @media (max-width: 900px) {
      .app-grid { grid-template-columns: 1fr; }
      .form-row { grid-template-columns: 1fr; }
      .pay-methods { grid-template-columns: 1fr; }
      .step-line { width: 30px; }
      .step-label { font-size: 0.7rem; max-width: 70px; }
      .app-form-card { padding: 24px; }
      .app-sidebar { position: static; }
    }
    @media (max-width: 480px) {
      .form-intro { padding: 28px 22px; }
      .form-intro h1 { font-size: 1.5rem; }
      .app-form-card { padding: 20px 16px; }
      .stepper { gap: 0; }
      .step-circle { width: 36px; height: 36px; font-size: .85rem; }
      .step-label { font-size: .6rem; max-width: 56px; }
      .step-line { width: 14px; }
      .form-actions { flex-direction: column-reverse; }
      .form-actions .btn-next, .form-actions .btn-prev, .form-actions .btn-submit { width: 100%; justify-content: center; }
    }

    .back-to-top { position: fixed; bottom: 100px; right: 30px; width: 50px; height: 50px; background: var(--o); color: #fff; border: none; border-radius: 50%; box-shadow: 0 4px 15px rgba(236,116,37,0.35); cursor: pointer; display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transform: translateY(20px); transition: all 0.3s; z-index: 9999; }
    .back-to-top.visible { opacity: 1; visibility: visible; transform: translateY(0); }
    .back-to-top:hover { background: #d05d15; transform: translateY(-3px); }
    .back-to-top svg { width: 24px; height: 24px; }
    @media (max-width: 768px) { .back-to-top { bottom: 96px; right: 20px; width: 44px; height: 44px; } }

    /* ── Step 6: Hostel & Accommodation ── */
    .acc-toggle { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 26px; }
    .acc-opt { flex: 1; min-width: 220px; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 16px 20px; border: 2px solid #e2e8f0; border-radius: 12px; font-weight: 700; font-size: 1rem; color: #64748b; cursor: pointer; transition: 0.25s; background: #fff; }
    .acc-opt input { display: none; }
    .acc-opt svg { width: 20px; height: 20px; }
    .acc-opt:hover { border-color: rgba(236,116,36,0.5); }
    .acc-opt.selected { border-color: var(--o); background: rgba(236,116,36,0.08); color: var(--o); box-shadow: 0 4px 14px rgba(236,116,36,0.15); }
    .acc-grid { display: grid; grid-template-columns: 1fr 320px; gap: 24px; align-items: start; }
    .acc-nobox { display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; gap: 12px; min-height: 180px; padding: 30px; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 14px; }
    .acc-nobox svg { width: 40px; height: 40px; color: var(--o); }
    .acc-nobox p { font-size: 0.95rem; color: #475569; line-height: 1.6; max-width: 340px; }
    .acc-info { background: linear-gradient(160deg, #fff7f0, #fdf1e6); border: 1px solid rgba(236,116,36,0.25); border-radius: 14px; padding: 22px 22px 20px; }
    .acc-info h4 { display: flex; align-items: center; gap: 8px; font-size: 1rem; color: var(--dark); margin-bottom: 14px; }
    .acc-info h4 svg { width: 18px; height: 18px; color: var(--o); }
    .acc-info ul { list-style: none; padding: 0; margin: 0 0 14px; display: flex; flex-direction: column; gap: 12px; }
    .acc-info li { display: flex; gap: 9px; font-size: 0.85rem; color: #475569; line-height: 1.5; }
    .acc-info li svg { width: 16px; height: 16px; color: var(--o); flex-shrink: 0; margin-top: 2px; }
    .acc-info-link { display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: var(--o); text-decoration: none; transition: gap 0.2s; }
    .acc-info-link:hover { gap: 10px; }
    .acc-info-link svg { width: 15px; height: 15px; }
    @media (max-width: 768px) { .acc-grid { grid-template-columns: 1fr; } }

    /* -- FORM VALIDATION -- */
    .form-group input.input-error, .form-group select.input-error, .form-group textarea.input-error { border-color: #dc2626; box-shadow: 0 0 0 4px rgba(220,38,38,0.12); }
    .field-error { display: block; color: #dc2626; font-size: 0.8rem; font-weight: 600; margin-top: 6px; line-height: 1.35; }
    .radio-group.error { border: 2px solid #dc2626; border-radius: 12px; padding: 8px 12px; }
    .upload-zone.blank-error { border-color: #dc2626; background: rgba(220,38,38,0.05); }
    .checkbox-agree.has-error { border-color: #dc2626; background: rgba(220,38,38,0.05); }
    input[type="checkbox"].input-error { outline: 2px solid #dc2626; outline-offset: 2px; border-radius: 4px; }
  </style>
  @include('components.seo')
</head>

<body>

  <!-- FLOATING BACKGROUND ICONS (animated) -->
  <div class="bg-float-wrap" aria-hidden="true">
    <svg class="bg-float bg-float--school" viewBox="0 0 290 290" fill="none" stroke="#642A7D" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
      <line x1="145" y1="15" x2="145" y2="70"/>
      <rect x="145" y="15" width="38" height="24" rx="2"/>
      <rect x="75" y="70" width="140" height="18" rx="3"/>
      <rect x="110" y="88" width="70" height="90" rx="3"/>
      <circle cx="145" cy="128" r="22"/>
      <line x1="145" y1="112" x2="145" y2="128"/>
      <line x1="145" y1="128" x2="158" y2="128"/>
      <rect x="20" y="178" width="250" height="16" rx="3"/>
      <rect x="20" y="194" width="90" height="76" rx="3"/>
      <rect x="32" y="206" width="28" height="20" rx="2"/>
      <rect x="70" y="206" width="28" height="20" rx="2"/>
      <rect x="32" y="236" width="28" height="20" rx="2"/>
      <rect x="70" y="236" width="28" height="20" rx="2"/>
      <rect x="180" y="194" width="90" height="76" rx="3"/>
      <rect x="192" y="206" width="28" height="20" rx="2"/>
      <rect x="230" y="206" width="28" height="20" rx="2"/>
      <rect x="192" y="236" width="28" height="20" rx="2"/>
      <rect x="230" y="236" width="28" height="20" rx="2"/>
      <rect x="122" y="228" width="46" height="42" rx="4"/>
      <line x1="10" y1="270" x2="280" y2="270"/>
    </svg>
    <svg class="bg-float bg-float--grad" viewBox="0 0 270 270" fill="none" stroke="#ec7424" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
      <path d="M30 180 Q30 100 135 90 Q240 100 240 180 L240 240 Q135 228 30 240 Z"/>
      <line x1="135" y1="90" x2="135" y2="240"/>
      <line x1="60" y1="150" x2="120" y2="144"/>
      <line x1="60" y1="168" x2="120" y2="163"/>
      <line x1="60" y1="186" x2="120" y2="182"/>
      <line x1="150" y1="144" x2="210" y2="150"/>
      <line x1="150" y1="163" x2="210" y2="168"/>
      <line x1="150" y1="182" x2="210" y2="186"/>
      <polygon points="135,30 210,62 135,94 60,62"/>
      <line x1="135" y1="94" x2="135" y2="116"/>
      <line x1="210" y1="62" x2="210" y2="90"/>
      <circle cx="210" cy="96" r="6"/>
      <rect x="95" y="220" width="80" height="22" rx="4"/>
      <line x1="95" y1="231" x2="175" y2="231"/>
    </svg>
  </div>

  <!-- COOKIE CONSENT -->
  <div id="cookie-banner" role="dialog" aria-label="Cookie consent" aria-live="polite">
    <div class="ck-inner">
      <div class="ck-left">
        <div class="ck-icon-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"/>
            <path d="M8.5 8.5v.01"/><path d="M16 15.5v.01"/><path d="M12 12v.01"/>
          </svg>
        </div>
        <div class="ck-copy">
          <strong>We use cookies</strong>
          <p>We use cookies to improve your experience, analyse site traffic, and personalise content. By continuing, you agree to our <a href="/">Privacy Policy</a>.</p>
        </div>
      </div>
      <div class="ck-actions">
        <button class="ck-btn-decline" id="ckDecline">Decline</button>
        <button class="ck-btn-accept" id="ckAccept">Accept All</button>
        <button class="ck-close" id="ckClose" aria-label="Close">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </div>
  </div>

  <!-- -- NAVBAR ---------------------------------------- -->
  <nav class="nav" id="mainNav">
    <div class="nav-inner">
      <a href="/" class="logo">
        <img loading="eager" decoding="async" src="images/gocare-institute-logo.png" width="100" />
      </a>
      <div class="nav-links">
        <div class="nav-item"><a href="/">Home</a></div>
        <div class="nav-item">
          <a href="about">About GoCare <i data-lucide="chevron-down"></i></a>
          <div class="dropdown about-mega">
            <div class="res-header">
              <span class="res-hd-icon"><i data-lucide="info"></i></span>
              <div class="res-hd-text"><strong>About GoCare</strong><small>Our story, values &amp; recognition</small></div>
            </div>
            <div class="res-body">
              <a href="about"><span class="rm-ico rm-ico--indigo"><i data-lucide="home"></i></span><span class="rm-text"><strong>About GoCare Institute</strong><small>Our history &amp; leadership</small></span></a>
              <a href="about#vision"><span class="rm-ico rm-ico--teal"><i data-lucide="flag"></i></span><span class="rm-text"><strong>Mission &amp; Vision</strong><small>What drives us forward</small></span></a>
              <a href="about#core-values"><span class="rm-ico rm-ico--rose"><i data-lucide="heart"></i></span><span class="rm-text"><strong>Core Values</strong><small>Principles we live by</small></span></a>
              <a href="about/accreditation"><span class="rm-ico rm-ico--purple"><i data-lucide="globe"></i></span><span class="rm-text"><strong>Accreditation &amp; Recognition</strong><small>Nationally &amp; internationally recognised</small></span></a>
              <a href="about/why-choose-us"><span class="rm-ico rm-ico--amber"><i data-lucide="check-circle"></i></span><span class="rm-text"><strong>Why Choose GoCare</strong><small>Stand-out reasons to join us</small></span></a>
            </div>
          </div>
        </div>
        <div class="nav-item">
          <a href="courses">Schools &amp; Programs <i data-lucide="chevron-down"></i></a>
          <div class="dropdown schools-mega">
            <div class="res-header">
              <span class="res-hd-icon"><i data-lucide="graduation-cap"></i></span>
              <div class="res-hd-text"><strong>Schools &amp; Programs</strong><small>Explore our diverse range of courses</small></div>
            </div>
            <div class="res-body">
              <a href="schools/medical-health-sciences"><span class="rm-ico rm-ico--rose"><i data-lucide="stethoscope"></i></span><span class="rm-text"><strong>School of Medical &amp; Health Sciences</strong><small>Healthcare &amp; clinical programs</small></span></a>
              <a href="schools/hospitality-management"><span class="rm-ico rm-ico--amber"><i data-lucide="utensils"></i></span><span class="rm-text"><strong>School of Hospitality Management</strong><small>Tourism, food &amp; front office</small></span></a>
              <a href="schools/social-sciences-business"><span class="rm-ico rm-ico--indigo"><i data-lucide="users"></i></span><span class="rm-text"><strong>School of Social Sciences &amp; Business</strong><small>Community &amp; business programs</small></span></a>
              <a href="schools/international-certifications"><span class="rm-ico rm-ico--teal"><i data-lucide="globe"></i></span><span class="rm-text"><strong>International Certifications</strong><small>Globally recognised qualifications</small></span></a>
              <a href="courses"><span class="rm-ico rm-ico--purple"><i data-lucide="search"></i></span><span class="rm-text"><strong>View All Courses</strong><small>Browse the full programme list</small></span></a>
            </div>
          </div>
        </div>
        <div class="nav-item">
          <a href="admissions#overview">Admissions <i data-lucide="chevron-down"></i></a>
          <div class="dropdown admissions-mega">
            <div class="res-header">
              <span class="res-hd-icon"><i data-lucide="clipboard-list"></i></span>
              <div class="res-hd-text"><strong>Admissions</strong><small>Your journey to GoCare starts here</small></div>
            </div>
            <div class="res-body">
              <a href="admissions#overview"><span class="rm-ico rm-ico--indigo"><i data-lucide="info"></i></span><span class="rm-text"><strong>Admissions Overview</strong><small>Everything you need to know</small></span></a>
              <a href="admissions#how-to-apply"><span class="rm-ico rm-ico--teal"><i data-lucide="file-edit"></i></span><span class="rm-text"><strong>How to Apply</strong><small>Step-by-step application guide</small></span></a>
              <a href="admissions#intakes"><span class="rm-ico rm-ico--amber"><i data-lucide="calendar"></i></span><span class="rm-text"><strong>Intakes &amp; Deadlines</strong><small>Upcoming intake dates</small></span></a>
              <a href="admissions#requirements"><span class="rm-ico rm-ico--orange"><i data-lucide="clipboard-list"></i></span><span class="rm-text"><strong>Entry Requirements</strong><small>Academic &amp; age criteria</small></span></a>
              <a href="admissions#fees"><span class="rm-ico rm-ico--purple"><i data-lucide="wallet"></i></span><span class="rm-text"><strong>Fees &amp; Payment Options</strong><small>Tuition, HELB &amp; bursaries</small></span></a>
              <a href="admissions#support"><span class="rm-ico rm-ico--rose"><i data-lucide="headphones"></i></span><span class="rm-text"><strong>Admissions Support</strong><small>Get help from our team</small></span></a>
              <a href="modes-of-study"><span class="rm-ico rm-ico--teal"><i data-lucide="book-open"></i></span><span class="rm-text"><strong>Modes of Study</strong><small>Full-time, part-time &amp; online</small></span></a>
              <a href="hostels-and-accommodation"><span class="rm-ico rm-ico--amber"><i data-lucide="bed"></i></span><span class="rm-text"><strong>Hostels &amp; Accommodation</strong><small>Comfortable student housing</small></span></a>
            </div>
          </div>
        </div>
        <div class="nav-item">
          <a href="industry-liaison">Industry Liaison <i data-lucide="chevron-down"></i></a>
          <div class="dropdown industry-mega">
            <div class="res-header">
              <span class="res-hd-icon"><i data-lucide="briefcase"></i></span>
              <div class="res-hd-text"><strong>Industry Liaison</strong><small>Partnerships, internships &amp; career services</small></div>
            </div>
            <div class="res-body">
              <a href="industry-liaison#our-role"><span class="rm-ico rm-ico--indigo"><i data-lucide="globe"></i></span><span class="rm-text"><strong>Our Role</strong><small>Bridging education &amp; industry</small></span></a>
              <a href="industrial-attachment"><span class="rm-ico rm-ico--orange"><i data-lucide="clipboard-list"></i></span><span class="rm-text"><strong>Internship &amp; Attachment</strong><small>Hands-on industry placements</small></span></a>
              <a href="institutional-partnerships"><span class="rm-ico rm-ico--teal"><i data-lucide="handshake"></i></span><span class="rm-text"><strong>Institutional Partnerships</strong><small>Our network of employers</small></span></a>
              <a href="careers"><span class="rm-ico rm-ico--amber"><i data-lucide="briefcase"></i></span><span class="rm-text"><strong>Career Services</strong><small>Jobs, placements &amp; guidance</small></span></a>
              <a href="courses/amca-usa-certification"><span class="rm-ico rm-ico--purple"><i data-lucide="globe-2"></i></span><span class="rm-text"><strong>International Certification</strong><small>USA &amp; global qualifications</small></span></a>
              <a href="alumni-network"><span class="rm-ico rm-ico--rose"><i data-lucide="users"></i></span><span class="rm-text"><strong>Alumni Network</strong><small>Stay connected after graduation</small></span></a>
              <a href="home-based-care"><span class="rm-ico rm-ico--teal"><i data-lucide="heart-pulse"></i></span><span class="rm-text"><strong>GoCare Health Solutions</strong><small>Professional healthcare services</small></span></a>
            </div>
          </div>
        </div>
        <div class="nav-item">
          <a href="#">Resources <i data-lucide="chevron-down"></i></a>
          <div class="dropdown resources-mega">
            <div class="res-header">
              <span class="res-hd-icon"><i data-lucide="layers"></i></span>
              <div class="res-hd-text"><strong>Resources &amp; Tools</strong><small>Everything you need to succeed at GoCare</small></div>
            </div>
            <div class="res-body">
              <a href="student-resources"><span class="rm-ico rm-ico--purple"><i data-lucide="book-open"></i></span><span class="rm-text"><strong>Student Resources</strong><small>Study materials &amp; guides</small></span></a>
              <a href="downloads"><span class="rm-ico rm-ico--orange"><i data-lucide="download"></i></span><span class="rm-text"><strong>Downloads</strong><small>Forms, brochures &amp; prospectus</small></span></a>
              <a href="blog"><span class="rm-ico rm-ico--amber"><i data-lucide="edit-3"></i></span><span class="rm-text"><strong>Blogs &amp; Articles</strong><small>Insights, tips &amp; news</small></span></a>
              <a href="#"><span class="rm-ico rm-ico--indigo"><i data-lucide="graduation-cap"></i></span><span class="rm-text"><strong>Student Portal</strong><small>Access your student account</small></span></a>
              <a href="student-testimonials-success-stories"><span class="rm-ico rm-ico--teal"><i data-lucide="message-circle"></i></span><span class="rm-text"><strong>Success Stories</strong><small>Graduate testimonials</small></span></a>
              <a href="gallery"><span class="rm-ico rm-ico--rose"><i data-lucide="images"></i></span><span class="rm-text"><strong>Photo Gallery</strong><small>Life &amp; moments at GoCare</small></span></a>
            </div>
          </div>
        </div>
        <div class="nav-item"><a href="contact">Contact Us</a></div>
      </div>
      <div class="nav-right">
        <a href="apply" class="nav-apply-btn">Start Your Journey <i data-lucide="arrow-right"></i></a>
        <div class="ham" id="hamBtn"><span></span><span></span><span></span></div>
      </div>
    </div>
    <div class="mob-menu" id="mobMenu">
      <a href="/">Home</a>
      <a href="about">About</a>
      <a href="courses">Schools &amp; Programs</a>
      <a href="admissions#overview">Admissions</a>
      <a href="industry-liaison">Industry Liaison</a>
      <a href="faqs">FAQs</a>
      <a href="downloads">Downloads</a>
      <a href="home-based-care">Home Based Care</a>
      <a href="gallery">Gallery</a>
      <a href="events">Events &amp; Open Days</a>
      <a href="blog">Blog &amp; Articles</a>
      <a href="contact">Contact Us</a>
      <a href="apply" class="btn btn-primary mob-cta">Start Your Journey <i data-lucide="arrow-right"></i></a>
    </div>
  </nav>

  <!-- -- SUB BAR --------------------------------------- -->
  <div class="subbar">
    <div class="wrap subbar-inner">
      <span class="subbar-tagline">Train With The Experts... Become an Expert!</span>
      <div class="subbar-portals">
        <a href="#" class="subbar-btn subbar-btn--student">Student Portal</a>
        <a href="#" class="subbar-btn subbar-btn--staff">Staff Portal</a>
      </div>
    </div>
  </div>

  <main class="app-page">
    <!-- PAGE HERO -->
    <section class="ph">
      <div class="ph-bg">
        <img loading="eager" decoding="async" src="images/new-images/Admissions-hero.jpeg" alt="Apply to GoCare Training Institute">
      </div>
      <div class="ph-overlay"></div>
      <div class="ph-inner">
        <div class="ph-content">
          <span class="ph-eyebrow"><i data-lucide="file-edit"></i> Admissions Open</span>
          <nav class="ph-breadcrumb" aria-label="Breadcrumb">
            <a href="/">Home</a><i data-lucide="chevron-right"></i><span>Apply Now</span>
          </nav>
          <h1>Application <em>Form</em></h1>
          <p id="heroSub">Registration and Admission Ongoing &mdash; Begin Your Journey</p>
          <div class="ph-btns">
            <a href="#formIntro" class="ph-btn-primary">Start Application <i data-lucide="arrow-right"></i></a>
            <a href="courses" class="ph-btn-ghost">Explore Courses <i data-lucide="arrow-right"></i></a>
          </div>
        </div>
      </div>
    </section>

    <div class="app-wrap">

      <!-- INTRO WELCOME SCREEN -->
      <div class="form-intro" id="formIntro">
        <h1>APPLICATION FORM - 2026</h1>
        <p class="intro-lead"><strong>Welcome! Follow the next steps as guided to kickstart your journey to academic empowerment and career success!</strong></p>
        <p><em>We are honored and delighted by your interest and decision to join us to pursue a course that will lead you to your dream career. We have established a tradition of excellence and high academic standards, and we commit to work endlessly to retain our position as your ideal training college of choice.</em></p>
        <p><em>Our college provides our students with a well-rounded training experience that meets international standards coupled with character development, holistic education and exposure to appropriate skills and competencies to empower, equip and prepare students who are confident, proactive and responsible citizens with the capacity to compete, access and pursue opportunities locally and globally.</em></p>
        <p><em>We are fully registered, regulated, licensed and accredited by the Ministry of Education, TVETA, TVET CDACC, NITA, KNEC and KHPOA. We are also an approved international training and examination center; USA, CANADA, IRELAND etc.</em></p>
        <!-- Shown only when a draft from this device is found. -->
        <div class="resume-box" id="resumeBox">
          <h4><i data-lucide="history"></i> You have a saved application</h4>
          <p id="resumeText">We kept everything you filled in on this device. Pick up where you left off, or start over.</p>
          <div class="resume-actions">
            <button type="button" class="resume-btn-primary" id="resumeContinueBtn">Resume where I left off</button>
            <button type="button" class="resume-btn-ghost" id="resumeFreshBtn">Start a new application</button>
          </div>
        </div>
        <button class="intro-begin-btn" id="introBeginBtn">Begin Application <i data-lucide="arrow-right"></i></button>
        <p class="autosave-note"><i data-lucide="save" style="width:14px;height:14px"></i> Your answers are saved on this device as you type, so you can close the page and come back.</p>
      </div>

      <!-- STEPPER -->
      <div class="stepper" id="stepper" style="display:none;">
        <div class="step-item"><div class="step-col"><div class="step-circle pending" data-circle="1">1</div><div class="step-label pending-label">Personal Details</div></div></div>
        <div class="step-line pending" data-line="1"></div>
        <div class="step-item"><div class="step-col"><div class="step-circle pending" data-circle="2">2</div><div class="step-label pending-label">Education</div></div></div>
        <div class="step-line pending" data-line="2"></div>
        <div class="step-item"><div class="step-col"><div class="step-circle pending" data-circle="3">3</div><div class="step-label pending-label">Course Selection</div></div></div>
        <div class="step-line pending" data-line="3"></div>
        <div class="step-item"><div class="step-col"><div class="step-circle pending" data-circle="4">4</div><div class="step-label pending-label">Documents</div></div></div>
        <div class="step-line pending" data-line="4"></div>
        <div class="step-item"><div class="step-col"><div class="step-circle pending" data-circle="5">5</div><div class="step-label pending-label">Payment</div></div></div>
        <div class="step-line pending" data-line="5"></div>
        <div class="step-item"><div class="step-col"><div class="step-circle pending" data-circle="6">6</div><div class="step-label pending-label">Accommodation</div></div></div>
        <div class="step-line pending" data-line="6"></div>
        <div class="step-item"><div class="step-col"><div class="step-circle pending" data-circle="7">7</div><div class="step-label pending-label">Review &amp; Submit</div></div></div>
      </div>

      <div class="resume-box" id="restoreNotice" style="margin-top:0;">
        <h4><i data-lucide="paperclip"></i> Re-attach your documents</h4>
        <p id="restoreNoticeText">Your answers were restored, but browsers cannot re-attach files for you. Open Step 4 and upload your documents again before submitting.</p>
        <div class="resume-actions">
          <button type="button" class="resume-btn-ghost" id="restoreNoticeDismiss">Got it</button>
        </div>
      </div>

      <!-- FORM GRID -->
      <div class="app-grid" id="appGrid" style="display:none;">
        <div>

          <!-- STEP 1: PERSONAL DETAILS -->
          <div class="form-step active" id="step1">
            <div class="app-form-card">
              <h2><i data-lucide="user" style="color:var(--o)"></i> Step 1: Personal Details</h2>
              <div class="form-group">
                <label>Full Name (As per ID/Passport) *</label>
                <input type="text" id="f_name" placeholder="e.g. John Kamau Mwangi">
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>Gender *</label>
                  <div class="radio-group">
                    <label><input type="radio" name="gender" value="Male"> Male</label>
                    <label><input type="radio" name="gender" value="Female"> Female</label>
                  </div>
                </div>
                <div class="form-group">
                  <label>Date of Birth *</label>
                  <input type="date" id="f_dob">
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>Nationality *</label>
                  {{-- Kenya is pinned to the top and skipped in the loop below,
                       so it never appears twice. The rest are the ISO 3166-1
                       countries in collated alphabetical order. --}}
                  <select id="f_nationality">
                    <option value="">Select</option>
                    <option selected>Kenya</option>
                    <option value="" disabled>──────────</option>
                    @foreach (config('countries') as $country)
                      @continue($country === 'Kenya')
                      <option>{{ $country }}</option>
                    @endforeach
                    <option value="" disabled>──────────</option>
                    <option>Other</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>ID/Passport Number *</label>
                  <input type="text" id="f_id" placeholder="e.g. 12345678">
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>Phone Number *</label>
                  <div class="phone-prefix"><span>+254</span><input type="tel" id="f_phone" placeholder="712345678"></div>
                </div>
                <div class="form-group">
                  <label>Email Address *</label>
                  <input type="email" id="f_email" placeholder="john@example.com">
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>County of Residence *</label>
                  <select id="f_county">
                    <option value="">Select County</option>
                    <option>Baringo</option>
                    <option>Bomet</option>
                    <option>Bungoma</option>
                    <option>Busia</option>
                    <option>Elgeyo-Marakwet</option>
                    <option>Embu</option>
                    <option>Garissa</option>
                    <option>Homa Bay</option>
                    <option>Isiolo</option>
                    <option>Kajiado</option>
                    <option>Kakamega</option>
                    <option>Kericho</option>
                    <option>Kiambu</option>
                    <option>Kilifi</option>
                    <option>Kirinyaga</option>
                    <option>Kisii</option>
                    <option>Kisumu</option>
                    <option>Kitui</option>
                    <option>Kwale</option>
                    <option>Laikipia</option>
                    <option>Lamu</option>
                    <option>Machakos</option>
                    <option>Makueni</option>
                    <option>Mandera</option>
                    <option>Marsabit</option>
                    <option>Meru</option>
                    <option>Migori</option>
                    <option>Mombasa</option>
                    <option>Murang'a</option>
                    <option>Nairobi</option>
                    <option>Nakuru</option>
                    <option>Nandi</option>
                    <option>Narok</option>
                    <option>Nyamira</option>
                    <option>Nyandarua</option>
                    <option>Nyeri</option>
                    <option>Samburu</option>
                    <option>Siaya</option>
                    <option>Taita-Taveta</option>
                    <option>Tana River</option>
                    <option>Tharaka-Nithi</option>
                    <option>Trans Nzoia</option>
                    <option>Turkana</option>
                    <option>Uasin Gishu</option>
                    <option>Vihiga</option>
                    <option>Wajir</option>
                    <option>West Pokot</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Home Address</label>
                  <input type="text" id="f_address" placeholder="Estate, Street, House No.">
                </div>
              </div>
              <div class="form-actions">
                <a href="/" class="btn-prev"><i data-lucide="arrow-left" style="width:18px;height:18px"></i> Cancel</a>
                <button type="button" class="btn-next" data-next="2">Save &amp; Continue <i data-lucide="arrow-right" style="width:18px;height:18px"></i></button>
              </div>
            </div>
          </div>

          <!-- STEP 2: EDUCATION -->
          <div class="form-step" id="step2">
            <div class="app-form-card">
              <h2><i data-lucide="graduation-cap" style="color:var(--o)"></i> Step 2: Education Background</h2>
              <div class="form-group">
                <label>Highest Qualification *</label>
                <select id="f_qual"><option value="">Select</option><option>KCPE</option><option selected>KCSE</option><option>Certificate</option><option>Diploma</option><option>Degree</option><option>Other</option></select>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>School Name *</label>
                  <input type="text" id="f_school" placeholder="e.g. Alliance High School">
                </div>
                <div class="form-group">
                  <label>Year of Completion *</label>
                  <select id="f_year"><option value="">Select Year</option><option>2025</option><option>2024</option><option>2023</option><option>2022</option><option>2021</option><option>2020</option><option>2019</option><option>Earlier</option></select>
                </div>
              </div>
              <div class="form-group">
                <label>KCSE Grade *</label>
                <select id="f_grade"><option value="">Select Grade</option><option>A</option><option>A-</option><option>B+</option><option>B</option><option>B-</option><option selected>C+</option><option>C</option><option>C-</option><option>D+</option><option>D</option><option>D-</option><option>E</option></select>
              </div>
              <div class="form-group">
                <label>Any Additional Qualifications / Certifications</label>
                <textarea id="f_addqual" rows="3" placeholder="List any additional certificates, diplomas, or professional qualifications..."></textarea>
              </div>
              <div class="form-actions">
                <button type="button" class="btn-prev" data-prev="1"><i data-lucide="arrow-left" style="width:18px;height:18px"></i> Previous</button>
                <button type="button" class="btn-next" data-next="3">Save &amp; Continue <i data-lucide="arrow-right" style="width:18px;height:18px"></i></button>
              </div>
            </div>
          </div>

          <!-- STEP 3: COURSE SELECTION -->
          <div class="form-step" id="step3">
            <div class="app-form-card">
              <h2><i data-lucide="book-open" style="color:var(--o)"></i> Step 3: Select Your Course</h2>
              <div class="form-group">
                <label>Preferred School *</label>
                <select id="f_school_sel">
                  <option value="">Select School</option>
                  <option value="medical">School of Medical &amp; Health Sciences</option>
                  <option value="hospitality">School of Hospitality Management</option>
                  <option value="social">School of Social Sciences &amp; Business Management</option>
                  <option value="intl">International Certifications</option>
                </select>
              </div>
              <div class="form-group">
                <label>Program *</label>
                <select id="f_program">
                  <option value="">Select Program</option>
                </select>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>Preferred Intake *</label>
                  <div style="display:flex;gap:10px;">
                    <select id="f_intake_month" style="flex:1.4;">
                      <option value="">Month</option>
                      <option>January</option>
                      <option>February</option>
                      <option>March</option>
                      <option>April</option>
                      <option>May</option>
                      <option>June</option>
                      <option>July</option>
                      <option>August</option>
                      <option>September</option>
                      <option>October</option>
                      <option>November</option>
                      <option>December</option>
                    </select>
                    <input type="number" id="f_intake_year" placeholder="Year" min="2024" max="2035" style="flex:1;min-width:0;">
                  </div>
                </div>
                <div class="form-group">
                  <label>Preferred Campus *</label>
                  <select id="f_campus"><option value="">Select Campus</option><option>Nairobi City Campus (CBD)</option><option>Thika Road Campus</option></select>
                </div>
              </div>
              <div class="form-group">
                <label>Preferred Study Mode *</label>
                <div class="radio-group">
                  <label><input type="radio" name="mode" value="Day"> Day</label>
                  <label><input type="radio" name="mode" value="Evening"> Evening</label>
                  <label><input type="radio" name="mode" value="Saturday"> Saturday</label>
                  <label><input type="radio" name="mode" value="Online/Distance"> Online/Distance</label>
                </div>
              </div>
              <div class="form-actions">
                <button type="button" class="btn-prev" data-prev="2"><i data-lucide="arrow-left" style="width:18px;height:18px"></i> Previous</button>
                <button type="button" class="btn-next" data-next="4">Save &amp; Continue <i data-lucide="arrow-right" style="width:18px;height:18px"></i></button>
              </div>
            </div>
          </div>

          <!-- STEP 4: DOCUMENTS -->
          <div class="form-step" id="step4">
            <div class="app-form-card">
              <h2><i data-lucide="file-up" style="color:var(--o)"></i> Step 4: Upload Documents</h2>
              <div class="form-group">
                <label>National ID / Passport Copy *</label>
                <div data-kind="id_copy" class="upload-zone" data-required="1"><i data-lucide="upload-cloud"></i><p class="upload-label">Click to upload or drag and drop</p><p>PDF, JPG or PNG (Max 5MB)</p><input type="file" accept=".pdf,.jpg,.jpeg,.png" style="display:none"></div>
              </div>
              <div class="form-group">
                <label>KCSE Result Slip / Certificate *</label>
                <div data-kind="result_slip" class="upload-zone" data-required="1"><i data-lucide="upload-cloud"></i><p class="upload-label">Click to upload or drag and drop</p><p>PDF, JPG or PNG (Max 5MB)</p><input type="file" accept=".pdf,.jpg,.jpeg,.png" style="display:none"></div>
              </div>
              <div class="form-group">
                <label>Passport-Size Photo *</label>
                <div data-kind="passport_photo" class="upload-zone" data-required="1"><i data-lucide="upload-cloud"></i><p class="upload-label">Click to upload or drag and drop</p><p>JPG or PNG (Max 2MB)</p><input type="file" accept=".jpg,.jpeg,.png" style="display:none"></div>
              </div>
              <div class="form-group">
                <label>Additional Certificates (Optional)</label>
                <div data-kind="certificate" class="upload-zone"><i data-lucide="upload-cloud"></i><p class="upload-label">Click to upload or drag and drop</p><p>PDF, JPG or PNG (Max 5MB each)</p><input type="file" accept=".pdf,.jpg,.jpeg,.png" multiple style="display:none"></div>
              </div>
              <div class="form-group">
                <p style="font-size:0.85rem;color:#64748b;"><i data-lucide="info" style="width:16px;height:16px;display:inline;vertical-align:middle;color:var(--o)"></i> Don&rsquo;t have documents yet? You can upload them later. Click &ldquo;Continue&rdquo; to proceed.</p>
              </div>
              <div class="form-actions">
                <button type="button" class="btn-prev" data-prev="3"><i data-lucide="arrow-left" style="width:18px;height:18px"></i> Previous</button>
                <button type="button" class="btn-next" data-next="5">Save &amp; Continue <i data-lucide="arrow-right" style="width:18px;height:18px"></i></button>
              </div>
            </div>
          </div>

          <!-- STEP 5: PAYMENT -->
          <div class="form-step" id="step5">
            <div class="app-form-card">
              <h2><i data-lucide="credit-card" style="color:var(--o)"></i> Step 5: Application Fee Payment</h2>
              <div class="form-group">
                <label>Payment Method</label>
                <div class="pay-methods">
                  <div class="pay-card selected" id="mpesaCard"><i data-lucide="smartphone"></i><h4>M-Pesa</h4><p>STK Push to your phone</p></div>
                </div>
              </div>

              @php $applicationFee = (int) config('gocare.application_fee'); @endphp
              <!-- M-Pesa STK Push prompt -->
              <div class="payment-info-box" id="mpesaInfo" style="display:block">
                <h4><i data-lucide="smartphone"></i> Pay via M-Pesa STK Push</h4>
                <div class="fee-summary" style="margin-bottom:14px;">
                  <div class="fee-row total"><span>Application Fee</span><span>KES {{ number_format($applicationFee) }}</span></div>
                </div>
                <p style="margin-bottom:14px;">Enter your M-Pesa registered phone number and click <strong>Send Payment Request</strong>. You will receive a prompt on your phone to confirm the payment.</p>
                <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                  <div style="flex:1;min-width:200px;">
                    <label style="font-size:.85rem;font-weight:600;color:#475569;display:block;margin-bottom:6px;">M-Pesa Phone Number</label>
                    <div class="phone-prefix">
                      <span>+254</span>
                      <input type="tel" id="f_mpesa_phone" placeholder="7XX XXX XXX" maxlength="9" pattern="[0-9]{9}" style="border-radius:0 10px 10px 0;">
                    </div>
                  </div>
                  <button type="button" id="mpesaPromptBtn" onclick="sendMpesaPrompt()" style="background:linear-gradient(135deg,#4caf50,#388e3c);color:#fff;border:none;padding:14px 24px;border-radius:10px;font-weight:700;font-size:.95rem;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:8px;">
                    <i data-lucide="send"></i> Send Payment Request
                  </button>
                </div>
                <div id="mpesaStatus" style="margin-top:12px;display:none;"></div>
              </div>

              <!-- Manual M-Pesa fallback, for when the STK prompt never arrives.
                   The applicant pays from their own M-Pesa menu, then confirms. -->
              <div style="margin-top:12px;">
                <button type="button" id="manualPayToggle" onclick="toggleManualPay()" style="background:none;border:none;padding:0;color:var(--o);font-weight:600;font-size:.9rem;cursor:pointer;text-decoration:underline;display:inline-flex;align-items:center;gap:6px;">
                  <i data-lucide="help-circle" style="width:16px;height:16px"></i> Prompt didn&rsquo;t arrive? Pay manually instead
                </button>
              </div>

              <div class="payment-info-box" id="manualPayBox" style="display:none;margin-top:14px;">
                <h4><i data-lucide="smartphone"></i> Pay manually via M-Pesa</h4>
                <ol style="margin:0 0 14px 18px;padding:0;font-size:.92rem;line-height:1.9;color:#334155;">
                  <li>Open <strong>M-Pesa</strong> on your phone</li>
                  <li>Select <strong>Lipa na M-Pesa</strong>, then <strong id="manualMethodLabel">Pay Bill</strong></li>
                  <li id="manualNumberRow">Business number: <strong id="manualNumber" style="letter-spacing:.5px;">&hellip;</strong></li>
                  <li id="manualAccountRow">Account number: <strong id="manualAccount" style="letter-spacing:.5px;">&hellip;</strong></li>
                  <li>Amount: <strong id="manualAmount">&hellip;</strong></li>
                  <li>Enter your M-Pesa PIN and confirm</li>
                </ol>
                <p style="margin-bottom:12px;font-size:.9rem;color:#475569;">You will receive an M-Pesa confirmation SMS with a code such as <strong>SHK3XY8ZT9</strong>. Enter that code below and click <strong>Completed</strong>.</p>
                <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                  <div style="flex:1;min-width:200px;">
                    <label style="font-size:.85rem;font-weight:600;color:#475569;display:block;margin-bottom:6px;">M-Pesa Confirmation Code</label>
                    <input type="text" id="f_manual_code" placeholder="e.g. SHK3XY8ZT9" maxlength="15" style="text-transform:uppercase;">
                  </div>
                  <button type="button" id="manualPaidBtn" onclick="confirmManualPayment()" style="background:linear-gradient(135deg,#4caf50,#388e3c);color:#fff;border:none;padding:14px 24px;border-radius:10px;font-weight:700;font-size:.95rem;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:8px;">
                    <i data-lucide="check-circle-2"></i> Completed
                  </button>
                </div>
                <div id="manualPayStatus" style="margin-top:12px;display:none;"></div>
              </div>
              <div class="form-group">
                <p style="font-size:0.85rem;color:#64748b;"><i data-lucide="info" style="width:16px;height:16px;display:inline;vertical-align:middle;color:var(--o)"></i> Payment must be completed before your application can be processed. Allow 24 hours for confirmation.</p>
              </div>
              <!-- Payment gate: Step 6 stays out of reach until the server
                   reports this application as paid. -->
              <div class="pay-gate" id="payGate">
                <i data-lucide="lock" class="pay-gate-icon"></i>
                <span id="payGateText">Payment not confirmed yet. Complete the M-Pesa payment above &mdash; the rest of the application unlocks the moment M-Pesa confirms it.</span>
              </div>
              <div class="form-actions">
                <button type="button" class="btn-prev" data-prev="4"><i data-lucide="arrow-left" style="width:18px;height:18px"></i> Previous</button>
                <button type="button" class="btn-next" data-next="6">Save &amp; Continue <i data-lucide="arrow-right" style="width:18px;height:18px"></i></button>
              </div>
            </div>
          </div>

          <!-- STEP 6: HOSTEL & ACCOMMODATION -->
          <div class="form-step" id="step6">
            <div class="app-form-card">
              <h2><i data-lucide="bed" style="color:var(--o)"></i> Step 6: Hostel &amp; Accommodation Preference</h2>
              <p style="font-size:.92rem;color:#64748b;margin-bottom:20px;">Would you like to apply for hostel accommodation at your campus?</p>

              <div class="acc-toggle">
                <label class="acc-opt" id="accYesOpt">
                  <input type="radio" name="accommodation" value="Yes" onchange="toggleAccommodation()">
                  <i data-lucide="check-circle-2"></i> Yes, I need accommodation
                </label>
                <label class="acc-opt selected" id="accNoOpt">
                  <input type="radio" name="accommodation" value="No" checked onchange="toggleAccommodation()">
                  <i data-lucide="x-circle"></i> No, I&rsquo;ll arrange my own
                </label>
              </div>

              <div class="acc-grid">
                <div class="acc-main">
                  <!-- YES panel -->
                  <div id="accYesPanel" style="display:none;">
                    <div class="form-group">
                      <label>Preferred Room Type *</label>
                      <select id="f_room_type">
                        <option value="">Select room type</option>
                        <option>4 Occupants &middot; Shared Room</option>
                        <option>6 Occupants &middot; Shared Room</option>
                        <option>8 Occupants &middot; Shared Room</option>
                      </select>
                    </div>
                    <div class="form-row">
                      <div class="form-group">
                        <label>Preferred Campus</label>
                        <select id="f_acc_campus">
                          <option value="">Select campus</option>
                          <option>Nairobi City Campus</option>
                          <option>Thika Road Campus &middot; Ruiru</option>
                        </select>
                      </div>
                      <div class="form-group">
                        <label>Check-in Intake</label>
                        <select id="f_acc_intake">
                          <option value="">Select intake</option>
                          <option>January</option>
                          <option>February</option>
                          <option>March</option>
                          <option>April</option>
                          <option>May</option>
                          <option>June</option>
                          <option>July</option>
                          <option>August</option>
                          <option>September</option>
                          <option>October</option>
                          <option>November</option>
                          <option>December</option>
                        </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <label>Special Requirements (Optional)</label>
                      <textarea id="f_acc_notes" rows="3" placeholder="e.g. ground-floor room, accessibility or dietary needs"></textarea>
                    </div>
                  </div>
                  <!-- NO panel -->
                  <div id="accNoPanel" class="acc-nobox">
                    <i data-lucide="home"></i>
                    <p>You have chosen <strong>not to book</strong> hostel accommodation. You can still request it later by contacting the admissions office.</p>
                  </div>
                </div>

                <aside class="acc-info">
                  <h4><i data-lucide="info"></i> Important Info</h4>
                  <ul>
                    <li><i data-lucide="check-circle-2"></i> Hostel rooms are allocated on a first-come, first-served basis.</li>
                    <li><i data-lucide="wifi"></i> All rooms include Wi-Fi &amp; study desks.</li>
                    <li><i data-lucide="wallet"></i> Accommodation fees are payable after admission confirmation.</li>
                  </ul>
                  <a href="hostels-and-accommodation" class="acc-info-link">View hostel details <i data-lucide="arrow-right"></i></a>
                </aside>
              </div>

              <div class="form-actions">
                <button type="button" class="btn-prev" data-prev="5"><i data-lucide="arrow-left" style="width:18px;height:18px"></i> Previous</button>
                <button type="button" class="btn-next" data-next="7">Proceed to Final Submission <i data-lucide="arrow-right" style="width:18px;height:18px"></i></button>
              </div>
            </div>
          </div>

          <!-- STEP 7: REVIEW & SUBMIT -->
          <div class="form-step" id="step7">
            <div class="app-form-card">
              <h2><i data-lucide="clipboard-check" style="color:var(--o)"></i> Step 7: Review Your Application</h2>

              <div class="review-section">
                <div class="review-header"><h3><i data-lucide="user"></i> Personal Details</h3><button type="button" data-goto="1">Edit</button></div>
                <div class="review-body">
                  <div class="review-row"><span class="review-label">Full Name</span><span class="review-value" id="rv_name">&mdash;</span></div>
                  <div class="review-row"><span class="review-label">Email</span><span class="review-value" id="rv_email">&mdash;</span></div>
                  <div class="review-row"><span class="review-label">Phone</span><span class="review-value" id="rv_phone">&mdash;</span></div>
                  <div class="review-row"><span class="review-label">Date of Birth</span><span class="review-value" id="rv_dob">&mdash;</span></div>
                  <div class="review-row"><span class="review-label">Gender</span><span class="review-value" id="rv_gender">&mdash;</span></div>
                </div>
              </div>

              <div class="review-section">
                <div class="review-header"><h3><i data-lucide="graduation-cap"></i> Education Background</h3><button type="button" data-goto="2">Edit</button></div>
                <div class="review-body">
                  <div class="review-row"><span class="review-label">Highest Level</span><span class="review-value" id="rv_qual">&mdash;</span></div>
                  <div class="review-row"><span class="review-label">School</span><span class="review-value" id="rv_school">&mdash;</span></div>
                  <div class="review-row"><span class="review-label">Year Completed</span><span class="review-value" id="rv_year">&mdash;</span></div>
                  <div class="review-row"><span class="review-label">Grade</span><span class="review-value" id="rv_grade">&mdash;</span></div>
                </div>
              </div>

              <div class="review-section">
                <div class="review-header"><h3><i data-lucide="book-open"></i> Course Selection</h3><button type="button" data-goto="3">Edit</button></div>
                <div class="review-body">
                  <div class="review-row"><span class="review-label">Course</span><span class="review-value" id="rv_program">&mdash;</span></div>
                  <div class="review-row"><span class="review-label">Campus</span><span class="review-value" id="rv_campus">&mdash;</span></div>
                  <div class="review-row"><span class="review-label">Intake</span><span class="review-value" id="rv_intake">&mdash;</span></div>
                  <div class="review-row"><span class="review-label">Mode of Study</span><span class="review-value" id="rv_mode">&mdash;</span></div>
                </div>
              </div>

              <div class="review-section">
                <div class="review-header"><h3><i data-lucide="file-text"></i> Payment</h3><button type="button" data-goto="5">Edit</button></div>
                <div class="review-body">
                  <div class="review-row"><span class="review-label">Payment Method</span><span class="review-value" id="rv_payment">&mdash;</span></div>
                </div>
              </div>

              <div class="review-section">
                <div class="review-header"><h3><i data-lucide="bed"></i> Hostel &amp; Accommodation</h3><button type="button" data-goto="6">Edit</button></div>
                <div class="review-body">
                  <div class="review-row"><span class="review-label">Apply for Hostel</span><span class="review-value" id="rv_acc">&mdash;</span></div>
                  <div class="review-row" id="rv_room_row" style="display:none;"><span class="review-label">Room Type</span><span class="review-value" id="rv_room">&mdash;</span></div>
                </div>
              </div>

              <div class="checkbox-agree">
                <input type="checkbox" id="agreeTerms">
                <label for="agreeTerms">I confirm that all the information provided is accurate and complete. I agree to the <a href="index#">Terms &amp; Conditions</a> and <a href="index#">Privacy Policy</a> of GoCare Training Institute. I understand that providing false information may lead to disqualification.</label>
              </div>

              <div class="form-actions">
                <button type="button" class="btn-prev" data-prev="6"><i data-lucide="arrow-left" style="width:18px;height:18px"></i> Previous</button>
                <button type="button" class="btn-submit" id="submitBtn" onclick="submitApplication()"><i data-lucide="send"></i> Submit Application</button>
              </div>
            </div>
          </div>

        </div>

        <!-- SIDEBAR -->
        <aside class="app-sidebar">
          <div class="sidebar-tips">
            <h3><i data-lucide="lightbulb"></i> Application Tips</h3>
            <ul>
              <li><i data-lucide="check-circle-2"></i> Use your name exactly as it appears on your ID.</li>
              <li><i data-lucide="check-circle-2"></i> Provide a valid email &mdash; admission updates go there.</li>
              <li><i data-lucide="check-circle-2"></i> Eligible students get a FREE second international certification.</li>
              <li><i data-lucide="check-circle-2"></i> You can save progress and return to any step.</li>
            </ul>
          </div>
          <div class="sidebar-card">
            <img loading="lazy" decoding="async" src="images/Nursing-Assistant.jpg" alt="GoCare student in training">
            <div class="sidebar-body">
              <h3>Need Help?</h3>
              <p>Our admissions team is ready to assist you through every step.</p>
              <ul>
                <li><i data-lucide="phone"></i> 0703 115 502 / 0745 229 485</li>
                <li><i data-lucide="mail"></i> admissions@gocareinstitute.ac.ke</li>
                <li><i data-lucide="clock"></i> Mon-Fri: 8AM - 5PM</li>
              </ul>
              <a href="contact" class="btn-next" style="display:flex;justify-content:center;margin-top:16px;text-decoration:none;font-size:0.9rem;">Contact Support</a>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </main>

  <!-- SUCCESS OVERLAY -->
  <div class="success-overlay" id="successOverlay">
    <div class="success-modal">
      <div class="success-icon"><i data-lucide="check"></i></div>
      <h2>Application Submitted!</h2>
      <p>Thank you for applying to GoCare Training Institute. Your application has been received successfully.</p>
      <p>Your Application ID:</p>
      <div class="app-id" id="appIdValue">GC-2026-04821</div>
      <p>We will review your application and contact you within 2-3 business days. Check your email for confirmation.</p>
      <a href="/" class="btn-next" style="margin-top:16px;justify-content:center;">Return to Homepage <i data-lucide="home" style="width:18px;height:18px"></i></a>
    </div>
  </div>

  <footer id="contactSection" class="footer">
    <!-- Decorative pattern overlay -->
    <div class="footer-pattern" aria-hidden="true"></div>

    <div class="footer-top footer-top--five">
      <!-- Brand Column -->
      <div class="footer-brand">
        <a href="/" class="logo">
          <img loading="eager" decoding="async" src="images/gocare-institute-logo-white.png" alt="GoCare Logo" style="height: 48px; width: auto; margin-bottom: 8px;">
        </a>
        <p>GoCare Training Institute offers expert-led healthcare education to shape your future.</p>
        <p class="footer-tagline"><em>Train with the Experts&hellip; Become an Expert!</em></p>
      </div>

      <!-- Schools & Programs Column -->
      <div class="footer-col">
        <h4>Schools &amp; Programs</h4>
        <ul class="footer-icon-list">
          <li><a href="schools/medical-health-sciences">School of Medical &amp; Health Sciences</a></li>
          <li><a href="schools/hospitality-management">School of Hospitality Management</a></li>
          <li><a href="schools/social-sciences-business">School of Social Sciences &amp; Business Management</a></li>
          <li><a href="schools/international-certifications">International Certifications</a></li>
        </ul>
      </div>

      <!-- Resources Column -->
      <div class="footer-col">
        <h4>Resources</h4>
        <ul class="footer-icon-list">
          <li><a href="student-resources">Student Resources</a></li>
          <li><a href="downloads">Downloads</a></li>
          <li><a href="blog">Blogs &amp; Articles</a></li>
          <li><a href="student-testimonials-success-stories">Testimonials &amp; Success Stories</a></li>
        </ul>
      </div>

      <!-- Quick Links Column -->
      <div class="footer-col">
        <h4>Quick Links</h4>
        <ul class="footer-icon-list">
          <li><a href="industrial-attachment">Industrial &amp; Field Attachment</a></li>
          <li><a href="student-support-services">Student Support Services</a></li>
          <li><a href="modes-of-study">Modes of Study</a></li>
          <li><a href="apply">Apply Now</a></li>
          <li><a href="docs/GoCare%20Training%20Institute%20Prospectus.pdf" target="_blank">Download Prospectus</a></li>
          <li><a href="#">Student Portal</a></li>
          <li><a href="#">Staff Portal</a></li>
          <li><a href="hostels-and-accommodation">Hostels &amp; Accommodation</a></li>
        </ul>
      </div>

      <!-- Contact Us Column -->
      <div class="footer-col footer-contact-col">
        <h4>Contact Us</h4>
        <div class="contact-group">
          <div class="contact-icon-label">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            <span class="contact-detail-text">Nairobi City Campus, Nairobi CBD, GatKim Complex, Temple Road</span>
          </div>
        </div>
        <div class="contact-group">
          <div class="contact-icon-label">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            <span class="contact-detail-text">Thika Road Campus, Eastern Bypass, Kamakis, Ruiru</span>
          </div>
        </div>
        <div class="contact-group">
          <div class="contact-icon-label">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
            </svg>
            <span class="contact-detail-text">0703 115 502 | 0745 229 485 | 0745 220 344</span>
          </div>
        </div>
        <div class="contact-group">
          <div class="contact-icon-label">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
              <polyline points="22,6 12,13 2,6" />
            </svg>
            <a href="mailto:info@gocareinstitute.ac.ke" class="contact-detail-text" style="color:inherit;text-decoration:none">info@gocareinstitute.ac.ke</a>
          </div>
        </div>
      </div>
    </div>
    <div class="footer-accred-bar">
      <p class="accred-tagline">TRAIN WITH THE EXPERTS &hellip; BECOME AN EXPERT!</p>
      <div class="accred-badges">
        <span class="accred-badge"><img loading="eager" decoding="async" src="images/partners/ministry of education.png" alt="Ministry of Education logo"></span>
        <span class="accred-badge"><img loading="eager" decoding="async" src="images/partners/TVETA.png" alt="TVETA logo"></span>
        <span class="accred-badge"><img loading="eager" decoding="async" src="images/partners/tveta curriculum.png" alt="TVET CDACC logo"></span>
        <span class="accred-badge"><img loading="eager" decoding="async" src="images/partners/national_industrial_training_authority_logo.png" alt="NITA logo"></span>
        <span class="accred-badge"><img loading="eager" decoding="async" src="images/partners/KHPOA.png" alt="KHPOA logo"></span>
<span class="accred-badge"><img loading="eager" decoding="async" src="images/partners/knec.png" alt="KNEC logo"></span>
        <span class="accred-badge"><img loading="eager" decoding="async" src="images/partners/amca.png" alt="AMCA logo"></span>
        <span class="accred-badge"><img loading="eager" decoding="async" src="images/partners/SDCC.png" alt="Skill Development Council Canada logo"></span>
        <span class="accred-badge"><img loading="eager" decoding="async" src="images/partners/American_Heart_Association_Logo.svg" alt="American Heart Association logo"></span>
      </div>
    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom footer-bottom--redesigned">
      <div class="footer-bottom-left">
        <div class="socials">
          <a href="https://www.facebook.com/GoCareTrainingInstitute/" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="Facebook">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" /></svg>
          </a>
          <a href="https://www.instagram.com/gocaretraininginstitute/" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="Instagram">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" /></svg>
          </a>
          <a href="https://x.com/GoCareInstitute" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="X">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" /></svg>
          </a>
          <a href="https://www.tiktok.com/@gocaretraininginstitute" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="TikTok">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.08-.14 1.62.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z" /></svg>
          </a>
          <a href="https://ke.linkedin.com/company/gocaretraininginstitute" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="LinkedIn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" /></svg>
          </a>
          <a href="https://www.youtube.com/@gocaretraininginstitute" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="YouTube">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" /></svg>
          </a>
        </div>
      </div>
      <p class="footer-copy">&copy; 2026 GoCare Training Institute. All rights reserved. | TVETA Accredited &amp; Licensed | TVET CDACC Approved | NITA Accredited | Globally Recognized</p>
    </div>
  </footer>

  <button class="back-to-top" id="backToTop" aria-label="Back to top">
    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15l-6-6-6 6"/></svg>
  </button>

  <script>
    /* -- MULTISTEP APPLICATION FORM -- */
    const STEP_TITLES = [
      'Personal Details', 'Education Background', 'Course Selection',
      'Documents Upload', 'Payment', 'Hostel &amp; Accommodation', 'Review &amp; Submit'
    ];
    let currentStep = 1;
    const totalSteps = 7;

    function renderStepper() {
      for (let i = 1; i <= totalSteps; i++) {
        const circle = document.querySelector('.step-circle[data-circle="' + i + '"]');
        const label = circle.parentElement.querySelector('.step-label');
        circle.classList.remove('done', 'active', 'pending');
        label.classList.remove('done-label', 'active-label', 'pending-label');
        if (i < currentStep) {
          circle.classList.add('done');
          circle.innerHTML = '<i data-lucide="check" style="width:20px;height:20px"></i>';
          label.classList.add('done-label');
        } else if (i === currentStep) {
          circle.classList.add('active');
          circle.textContent = i;
          label.classList.add('active-label');
        } else {
          circle.classList.add('pending');
          circle.textContent = i;
          label.classList.add('pending-label');
        }
      }
      for (let j = 1; j < totalSteps; j++) {
        const line = document.querySelector('.step-line[data-line="' + j + '"]');
        line.classList.remove('done', 'active', 'pending');
        if (j < currentStep) line.classList.add('done');
        else if (j === currentStep) line.classList.add('active');
        else line.classList.add('pending');
      }
      document.getElementById('heroSub').innerHTML = 'Step ' + currentStep + ' of ' + totalSteps + ' &mdash; ' + STEP_TITLES[currentStep - 1];
      if (window.lucide) lucide.createIcons();
    }

    function showStep(n) {
      currentStep = n;
      document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
      document.getElementById('step' + n).classList.add('active');
      if (n === 7) populateReview();
      if (n === 5) refreshPaymentGate(); else stopPayGateWatch();
      renderStepper();
      scrollToFormTop();
    }

    /* Bring the top of the form area into view only when it is off-screen,
       so moving between steps no longer yanks the page back to the top. */
    function scrollToFormTop() {
      var anchor = document.getElementById('stepper');
      if (!anchor || anchor.style.display === 'none') anchor = document.getElementById('appGrid');
      if (!anchor) return;
      var margin = 20;
      var top = anchor.getBoundingClientRect().top;
      // Comfortable zone: form top already near the top of the viewport -> don't scroll.
      if (top >= 0 && top <= 120) return;
      var target = window.pageYOffset + top - margin;
      window.scrollTo({ top: Math.max(0, target), behavior: 'smooth' });
    }


    function val(id) { var el = document.getElementById(id); return el && el.value ? el.value : ''; }
    function radioVal(name) { var el = document.querySelector('input[name="' + name + '"]:checked'); return el ? el.value : ''; }
    function setRv(id, v) { document.getElementById(id).textContent = v || '—'; }

    /* -- FORM VALIDATION -- */
    /* Resolve the block-level container where a field's error message belongs.
       Handles phone-prefix wrappers (so the message shows below, not inside the
       flex row) and radio groups. */
    function errorContainer(el) {
      if (!el) return null;
      if (el.type === 'radio') {
        var g = el.closest('.radio-group');
        return (g && g.parentElement) || el.parentElement;
      }
      var pp = el.closest('.phone-prefix');
      if (pp) return pp.parentElement || pp;
      return el.closest('.form-group') || el.parentElement;
    }

    /* Fields sharing one .form-group (Preferred Intake holds both a month and
       a year) each need their own message slot, or the second setError call
       silently overwrites the first and only one problem is ever reported. */
    function errorSlot(box, key) {
      var sel = ':scope > .field-error[data-for="' + key + '"]';
      var err = box.querySelector(sel);
      if (!err) {
        err = document.createElement('span');
        err.className = 'field-error';
        err.setAttribute('data-for', key);
        box.appendChild(err);
      }
      return err;
    }

    function setError(target, msg) {
      var el = (typeof target === 'string') ? document.getElementById(target) : target;
      if (!el) return;
      el.classList.add('input-error');
      var box = errorContainer(el);
      if (!box) return;
      errorSlot(box, el.id || el.name || 'field').textContent = msg;
    }

    function setRadioError(name, msg) {
      var grp = null;
      document.querySelectorAll('input[name="' + name + '"]').forEach(function (r) {
        r.classList.add('input-error');
        if (!grp) grp = r.closest('.radio-group');
      });
      if (!grp) return;
      grp.classList.add('error');
      var box = grp.parentElement || grp;
      errorSlot(box, name).textContent = msg;
    }

    function clearElError(el) {
      if (!el) return;
      if (el.type === 'radio') {
        document.querySelectorAll('input[name="' + el.name + '"]').forEach(function (x) { x.classList.remove('input-error'); });
        var grp = el.closest('.radio-group');
        if (grp) {
          grp.classList.remove('error');
          var gbox = grp.parentElement || grp;
          var ge = gbox.querySelector(':scope > .field-error');
          if (ge) ge.remove();
        }
        return;
      }
      // Uploads: also drop the required-state styling on the drop zone.
      if (el.type === 'file') {
        var z = el.closest('.upload-zone');
        if (z) z.classList.remove('blank-error');
      }
      el.classList.remove('input-error');
      var box = errorContainer(el);
      if (box) {
        var key = el.id || el.name || 'field';
        var e = box.querySelector(':scope > .field-error[data-for="' + key + '"]')
          || box.querySelector(':scope > .field-error:not([data-for])');
        if (e) e.remove();
      }
    }

    function validateStep1() {
      var ok = true;
      var name = val('f_name').trim();
      if (!name) { setError(f_name, 'Full name is required.'); ok = false; }
      else if (name.length < 3 || /\d/.test(name)) { setError(f_name, 'Enter your full legal name (letters only).'); ok = false; }

      if (!radioVal('gender')) { setRadioError('gender', 'Please select your gender.'); ok = false; }

      var dob = val('f_dob');
      if (!dob) { setError(f_dob, 'Date of birth is required.'); ok = false; }
      else {
        var birth = new Date(dob);
        var age = (Date.now() - birth.getTime()) / 31557600000;
        if (birth.getTime() > Date.now()) { setError(f_dob, 'Date of birth cannot be in the future.'); ok = false; }
        else if (age < 14) { setError(f_dob, 'Applicants must be at least 14 years old.'); ok = false; }
        else if (age > 100) { setError(f_dob, 'Please double-check your date of birth.'); ok = false; }
      }

      if (!val('f_nationality')) { setError(f_nationality, 'Please select your nationality.'); ok = false; }

      var id = val('f_id').trim();
      if (!id) { setError(f_id, 'ID/Passport number is required.'); ok = false; }
      else if (!/^[A-Za-z0-9/-]{4,20}$/.test(id)) { setError(f_id, 'Enter a valid ID or passport number.'); ok = false; }

      var phone = val('f_phone').replace(/\s/g, '');
      if (!phone) { setError(f_phone, 'Phone number is required.'); ok = false; }
      else if (!/^(7|1)\d{8}$/.test(phone)) { setError(f_phone, 'Enter a 9-digit number, e.g. 712345678.'); ok = false; }

      var email = val('f_email').trim();
      if (!email) { setError(f_email, 'Email address is required.'); ok = false; }
      else if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) { setError(f_email, 'Enter a valid email address, e.g. john@example.com.'); ok = false; }

      if (!val('f_county')) { setError(f_county, 'Please select your county of residence.'); ok = false; }
      return ok;
    }

    function validateStep2() {
      var ok = true;
      if (!val('f_qual')) { setError(f_qual, 'Select your highest qualification.'); ok = false; }
      if (!val('f_school').trim()) { setError(f_school, 'School name is required.'); ok = false; }
      if (!val('f_year')) { setError(f_year, 'Select the year of completion.'); ok = false; }
      if (!val('f_grade')) { setError(f_grade, 'Select your KCSE grade.'); ok = false; }
      return ok;
    }

    function validateStep3() {
      var ok = true;
      if (!val('f_school_sel')) { setError(f_school_sel, 'Select your preferred school.'); ok = false; }
      if (!val('f_program')) { setError(f_program, 'Select your program of interest.'); ok = false; }
      if (!val('f_intake_month')) { setError(f_intake_month, 'Select an intake month.'); ok = false; }
      var yr = val('f_intake_year').trim();
      var currentYear = new Date().getFullYear();
      if (!yr) { setError(f_intake_year, 'Enter the intake year.'); ok = false; }
      else if (!/^\d{4}$/.test(yr) || +yr < currentYear || +yr > currentYear + 3) {
        setError(f_intake_year, 'Enter a year between ' + currentYear + ' and ' + (currentYear + 3) + '.');
        ok = false;
      }
      if (!val('f_campus')) { setError(f_campus, 'Select your preferred campus.'); ok = false; }
      if (!radioVal('mode')) { setRadioError('mode', 'Select your preferred study mode.'); ok = false; }
      return ok;
    }

    function validateStep4() {
      var ok = true;
      document.querySelectorAll('#step4 .upload-zone[data-required="1"]').forEach(function (zone) {
        var input = zone.querySelector('input[type="file"]');
        // What the SERVER has, not what the file picker holds: a file that
        // failed to upload leaves the picker populated and the server empty.
        var kind = zone.dataset.kind;
        var has = !!(kind && uploadedDocs[kind] && uploadedDocs[kind].length);
        var err = zone.parentElement.querySelector('.field-error');
        if (!has) {
          zone.classList.add('blank-error');
          if (!err) { err = document.createElement('span'); err.className = 'field-error'; zone.parentElement.appendChild(err); }
          err.textContent = 'This document is required.';
          ok = false;
        } else {
          zone.classList.remove('blank-error');
          if (err) err.remove();
        }
      });
      return ok;
    }

    function validateStep5() {
      // A confirmed payment settles this step. Never strand an applicant who
      // has already parted with the money over a field they skipped -- someone
      // who paid by hand may never have typed the STK number at all.
      if (paymentConfirmed) return true;

      var ok = true;
      var mpesa = val('f_mpesa_phone').replace(/\s/g, '');
      if (!mpesa) { setError(f_mpesa_phone, 'M-Pesa number is required to pay the application fee.'); ok = false; }
      else if (!/^(7|1)\d{8}$/.test(mpesa)) { setError(f_mpesa_phone, 'Enter a valid 9-digit M-Pesa number, e.g. 712345678.'); ok = false; }

      // Nothing past this step opens until M-Pesa has confirmed the fee.
      if (!paymentConfirmed) {
        refreshPaymentGate();
        var gate = document.getElementById('payGate');
        if (gate && gate.scrollIntoView) gate.scrollIntoView({ behavior: 'smooth', block: 'center' });
        ok = false;
      }
      return ok;
    }

    function validateStep6() {
      var ok = true;
      if (radioVal('accommodation') === 'Yes' && !val('f_room_type')) {
        setError(f_room_type, 'Select your preferred room type.');
        ok = false;
      }
      return ok;
    }

    function validateStep7() {
      var wrap = document.getElementById('agreeTerms').closest('.checkbox-agree');
      var err = wrap.querySelector('.field-error');
      if (err) err.remove();
      if (!document.getElementById('agreeTerms').checked) {
        document.getElementById('agreeTerms').classList.add('input-error');
        wrap.classList.add('has-error');
        err = document.createElement('span');
        err.className = 'field-error';
        err.textContent = 'You must agree to the terms and conditions before submitting.';
        wrap.appendChild(err);
        return false;
      }
      document.getElementById('agreeTerms').classList.remove('input-error');
      wrap.classList.remove('has-error');
      return true;
    }

    var STEP_VALIDATORS = { 1: validateStep1, 2: validateStep2, 3: validateStep3, 4: validateStep4, 5: validateStep5, 6: validateStep6, 7: validateStep7 };

    /* A blocked Next reads as a dead button when the offending field sits above
       the fold on a long step. Put the first one on screen and in focus.
       The payment gate is left out on purpose: validateStep5 scrolls to it
       itself, and it is not a field the applicant can fill in. */
    function revealFirstError(step) {
      var scope = document.getElementById('step' + step);
      if (!scope) return;
      var first = scope.querySelector('.input-error, .radio-group.error, .upload-zone.blank-error');
      if (!first) return;
      if (first.scrollIntoView) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
      // Radio groups and upload zones are not focusable; reach for the control.
      var focusable = first.matches('input, select, textarea') ? first : first.querySelector('input, select, textarea');
      if (focusable && focusable.type !== 'file') {
        setTimeout(function () { try { focusable.focus({ preventScroll: true }); } catch (e) { focusable.focus(); } }, 320);
      }
    }

    function populateReview() {
      setRv('rv_name', val('f_name'));
      setRv('rv_email', val('f_email'));
      setRv('rv_phone', val('f_phone') ? '+254 ' + val('f_phone') : '');
      setRv('rv_dob', val('f_dob'));
      setRv('rv_gender', radioVal('gender'));
      setRv('rv_qual', val('f_qual'));
      setRv('rv_school', val('f_school'));
      setRv('rv_year', val('f_year'));
      setRv('rv_grade', val('f_grade'));
      setRv('rv_program', val('f_program'));
      setRv('rv_campus', val('f_campus'));
      setRv('rv_intake', val('f_intake'));
      setRv('rv_mode', radioVal('mode'));
      var pay = document.querySelector('.pay-card.selected h4');
      setRv('rv_payment', pay ? pay.textContent : '');
      var acc = radioVal('accommodation') || 'No';
      setRv('rv_acc', acc);
      var roomRow = document.getElementById('rv_room_row');
      if (acc === 'Yes') {
        roomRow.style.display = '';
        setRv('rv_room', val('f_room_type'));
      } else {
        roomRow.style.display = 'none';
      }
    }

    function toggleAccommodation() {
      var wantsAcc = radioVal('accommodation') === 'Yes';
      document.getElementById('accYesPanel').style.display = wantsAcc ? 'block' : 'none';
      document.getElementById('accNoPanel').style.display = wantsAcc ? 'none' : 'flex';
      document.getElementById('accYesOpt').classList.toggle('selected', wantsAcc);
      document.getElementById('accNoOpt').classList.toggle('selected', !wantsAcc);
      if (window.lucide) lucide.createIcons();
    }

    function selectPay(card) {
      document.querySelectorAll('.pay-card').forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
      document.getElementById('mpesaInfo').style.display = 'none';
      const title = card.querySelector('h4').textContent;
      if (title === 'M-Pesa') document.getElementById('mpesaInfo').style.display = 'block';
      if (window.lucide) lucide.createIcons();
    }

    var mpesaPollTimer = null;
    var manualVerifyTimer = null;
    var mpesaReference = null;

    /* Document ids the server has accepted, by kind. validateStep4 reads this
       rather than the file inputs, so a failed upload cannot pass as done. */
    var uploadedDocs = {};

    /* -- PAYMENT GATE ------------------------------------------------
       Steps 6 and 7 stay out of reach until the server reports this
       application as paid. The flag below only drives the UI: the
       decision is the server's /applications/status answer, and
       /applications/submit refuses an unpaid application outright, so
       flipping this in a console buys nothing. */
    var paymentConfirmed = false;
    var payGateTimer = null;
    var PAY_GATE_LOCKED = 'Payment not confirmed yet. Complete the M-Pesa payment above &mdash; the rest of the application unlocks the moment M-Pesa confirms it.';
    var PAY_GATE_WAITING = '&#x23F3; Your payment is recorded and waiting for M-Pesa to confirm it. This step unlocks by itself as soon as that lands &mdash; you can close the page and come back, your answers are saved on this device.';

    function setPayGate(state, html) {
      var box = document.getElementById('payGate');
      var text = document.getElementById('payGateText');
      if (!box || !text) return;
      box.classList.remove('pay-gate--waiting', 'pay-gate--paid');
      if (state === 'waiting') box.classList.add('pay-gate--waiting');
      if (state === 'paid') box.classList.add('pay-gate--paid');
      // Lucide swaps the <i> for an <svg> on render, and an <svg> will not
      // re-render from a changed data-lucide -- so hand it a fresh <i>.
      var icon = box.querySelector('.pay-gate-icon');
      if (icon && icon.parentNode) {
        var fresh = document.createElement('i');
        fresh.className = 'pay-gate-icon';
        fresh.setAttribute('data-lucide', state === 'paid' ? 'check-circle-2' : (state === 'waiting' ? 'clock' : 'lock'));
        icon.parentNode.replaceChild(fresh, icon);
      }
      text.innerHTML = html;
      if (window.lucide) lucide.createIcons();
    }

    function markPaid(reference) {
      if (reference) mpesaReference = reference;
      paymentConfirmed = true;
      stopPayGateWatch();
      setPayGate('paid', '&#10003; Payment confirmed'
        + (mpesaReference ? ' &mdash; reference <strong>' + mpesaReference + '</strong>' : '')
        + '. You can continue to the next step.');
      saveDraft();
    }

    async function checkPaymentStatus() {
      if (!mpesaReference) return null;
      try {
        const res = await fetch('/applications/status/' + encodeURIComponent(mpesaReference), {
          headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) return null;
        const data = await res.json();
        return data.payment_status || null;
      } catch (err) {
        return null;
      }
    }

    async function refreshPaymentGate() {
      if (paymentConfirmed) { markPaid(); return; }
      if (!mpesaReference) { setPayGate('locked', PAY_GATE_LOCKED); return; }

      var status = await checkPaymentStatus();
      if (status === 'paid') { markPaid(); return; }

      if (status === 'awaiting_verification') {
        setPayGate('waiting', PAY_GATE_WAITING);
      } else if (status === 'failed') {
        setPayGate('locked', '&#x26A0; The last payment attempt failed or was cancelled. Send the request again above, or pay manually.');
      } else {
        setPayGate('locked', PAY_GATE_LOCKED);
      }
      startPayGateWatch();
    }

    /* A confirmation can land minutes after the STK poll gives up, so keep a
       slow watch running while the applicant sits on the Payment step. */
    /* Redraw the payment step itself -- status line and button -- so a
       confirmation arriving after the fast poll gave up is not left sitting
       behind a stale "No confirmation yet". The gate box was already updated
       quietly; this is what the applicant is actually looking at. */
    function refreshPaymentUi(status) {
      var btn = document.getElementById('mpesaPromptBtn');
      if (!btn) return;

      if (status === 'paid') {
        setMpesaStatus('#2e7d32', '&#10003; Payment received. Reference ' + mpesaReference + '.');
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="check"></i> Paid';
      } else if (status === 'awaiting_verification') {
        setMpesaStatus('#1565c0', '&#x23F3; Your payment is recorded and waiting for M-Pesa to confirm it. Reference ' + mpesaReference + '.');
      } else if (status === 'failed') {
        setMpesaStatus('#c0392b', '&#x26A0; Payment failed or was cancelled. Tap the button to try again, or pay manually below.');
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="send"></i> Send Payment Request';
      } else {
        setMpesaStatus('#b26a00', '&#x23F3; Still waiting for M-Pesa. This keeps checking on its own &mdash; leave the page open, or pay manually below.');
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="send"></i> Send Payment Request';
      }

      if (window.lucide) lucide.createIcons();
    }

    /* Runs for as long as the applicant is on the payment step: there is no
       give-up. Paused while the tab is hidden so an abandoned tab does not
       poll for ever, and re-checked the moment it comes back. */
    function startPayGateWatch() {
      if (payGateTimer || paymentConfirmed || !mpesaReference) return;

      payGateTimer = setInterval(async function () {
        if (document.hidden) return;

        var status = await checkPaymentStatus();
        if (!status) return;

        if (status === 'paid') {
          markPaid();
          refreshPaymentUi('paid');
          return;
        }

        if (status === 'awaiting_verification') setPayGate('waiting', PAY_GATE_WAITING);

        // Only redraw the step once the fast poll has handed over, or the two
        // would fight over the same status line for the first two minutes.
        if (!mpesaPollTimer) refreshPaymentUi(status);
      }, 6000);
    }

    document.addEventListener('visibilitychange', function () {
      if (document.hidden || !payGateTimer || paymentConfirmed) return;

      checkPaymentStatus().then(function (status) {
        if (status === 'paid') { markPaid(); refreshPaymentUi('paid'); }
        else if (status && !mpesaPollTimer) refreshPaymentUi(status);
      });
    });

    function stopPayGateWatch() {
      if (payGateTimer) { clearInterval(payGateTimer); payGateTimer = null; }
    }

    function setMpesaStatus(color, html) {
      var status = document.getElementById('mpesaStatus');
      status.style.display = 'block';
      status.innerHTML = '<span style="color:' + color + ';font-weight:600;">' + html + '</span>';
    }

    function resetMpesaButton() {
      var btn = document.getElementById('mpesaPromptBtn');
      btn.disabled = false;
      btn.innerHTML = '<i data-lucide="send"></i> Send Payment Request';
      if (window.lucide) lucide.createIcons();
    }

    async function sendMpesaPrompt() {
      const rawPhone = (document.getElementById('f_mpesa_phone').value || '').trim().replace(/\s/g, '');
      const btn = document.getElementById('mpesaPromptBtn');

      if (!/^[0-9]{9}$/.test(rawPhone)) {
        setMpesaStatus('#c0392b', '&#x26A0; Please enter a valid 9-digit M-Pesa number (e.g. 712345678).');
        return;
      }

      if (mpesaPollTimer) { clearInterval(mpesaPollTimer); mpesaPollTimer = null; }

      const phone = '254' + rawPhone;
      btn.disabled = true;
      btn.innerHTML = '<i data-lucide="loader"></i> Sending&hellip;';
      if (window.lucide) lucide.createIcons();
      setMpesaStatus('#1565c0', '&#x23F3; Requesting M-Pesa prompt&hellip;');

      try {
        const response = await fetch('/applications', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            phone: phone,
            // The fee is set server-side (config gocare.application_fee).
            name: val('f_name'),
            email: val('f_email'),
            course: val('f_program')
          })
        });

        const data = await response.json();

        if (!response.ok) {
          let msg = data.error || 'Failed to initiate payment. Please try again.';
          if (data.debug) {
            msg += '<br><small style="font-weight:400;">' + data.debug.message + '</small>';
            console.error('[mpesa]', data.debug);
          }
          setMpesaStatus('#c0392b', '&#x26A0; ' + msg);
          resetMpesaButton();
          return;
        }

        mpesaReference = data.reference;
        saveDraft();

        // store() short-circuits when this application is already settled.
        if (data.payment_status === 'paid') {
          markPaid(data.reference);
          setMpesaStatus('#2e7d32', '&#10003; This application is already paid. Reference ' + data.reference + '.');
          btn.innerHTML = '<i data-lucide="check"></i> Paid';
          if (window.lucide) lucide.createIcons();
          return;
        }

        // No prompt is sent when a payment from this number is already being
        // confirmed. Saying "check your phone" there sends the applicant
        // looking for a prompt that does not exist.
        if (data.payment_status === 'awaiting_verification') {
          setMpesaStatus('#1565c0', '&#x23F3; ' + data.message);
          btn.innerHTML = '<i data-lucide="clock"></i> Awaiting confirmation';
          if (window.lucide) lucide.createIcons();
          refreshPaymentGate();
          return;
        }

        setMpesaStatus('#1565c0', '&#x23F3; STK Push sent to +' + phone + '. Check your phone and enter your M-Pesa PIN to complete payment.');
        btn.innerHTML = '<i data-lucide="smartphone"></i> Check your phone';
        if (window.lucide) lucide.createIcons();
        pollMpesaStatus(data.reference);
      } catch (err) {
        console.error('[mpesa]', err);
        setMpesaStatus('#c0392b', '&#x26A0; A network error occurred. Please try again.');
        resetMpesaButton();
      }
    }

    function setManualStatus(color, html) {
      var box = document.getElementById('manualPayStatus');
      box.style.display = 'block';
      box.style.color = color;
      box.style.fontWeight = '600';
      box.style.fontSize = '.9rem';
      box.innerHTML = html;
    }

    async function toggleManualPay() {
      var box = document.getElementById('manualPayBox');

      if (box.style.display === 'block') {
        box.style.display = 'none';
        return;
      }

      box.style.display = 'block';

      // Ask the server for the paybill/till and this applicant's account
      // number. It creates the application row if the STK step never ran, so
      // there is always a reference to pay against.
      try {
        const res = await fetch('/applications/manual', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            phone: '254' + (document.getElementById('f_mpesa_phone').value || '').trim().replace(/\s/g, '')
          })
        });

        const data = await res.json();

        if (!res.ok) {
          setManualStatus('#c0392b', '&#x26A0; ' + (data.error || 'Could not load payment details. Please try again.'));
          return;
        }

        mpesaReference = data.reference;
        saveDraft();

        var isTill = data.method === 'till';
        document.getElementById('manualMethodLabel').textContent = isTill ? 'Buy Goods and Services' : 'Pay Bill';
        document.getElementById('manualNumberRow').childNodes[0].nodeValue = isTill ? 'Till number: ' : 'Business number: ';
        document.getElementById('manualNumber').textContent = data.number;
        document.getElementById('manualAmount').textContent = 'KES ' + Number(data.amount).toLocaleString();

        // A till has no account number field on the handset, so the reference
        // cannot be quoted there — the confirmation code is the only link back.
        document.getElementById('manualAccountRow').style.display = isTill ? 'none' : '';
        document.getElementById('manualAccount').textContent = data.account;
      } catch (err) {
        console.error('[mpesa-manual]', err);
        setManualStatus('#c0392b', '&#x26A0; A network error occurred. Please try again.');
      }
    }

    async function confirmManualPayment() {
      var input = document.getElementById('f_manual_code');
      var code = (input.value || '').trim().toUpperCase();
      var btn = document.getElementById('manualPaidBtn');

      if (!/^[A-Z0-9]{6,15}$/.test(code)) {
        setManualStatus('#c0392b', '&#x26A0; Enter the M-Pesa confirmation code from your SMS, e.g. SHK3XY8ZT9.');
        return;
      }

      btn.disabled = true;
      btn.innerHTML = '<i data-lucide="loader"></i> Submitting&hellip;';
      if (window.lucide) lucide.createIcons();

      try {
        const res = await fetch('/applications/paid', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({ transaction_code: code })
        });

        const data = await res.json();

        if (!res.ok) {
          setManualStatus('#c0392b', '&#x26A0; ' + (data.error || 'Could not record your payment. Please try again.'));
          btn.disabled = false;
          btn.innerHTML = '<i data-lucide="check-circle-2"></i> Completed';
          if (window.lucide) lucide.createIcons();
          return;
        }

        // Stop the STK poll: the applicant has settled this by hand.
        if (mpesaPollTimer) { clearInterval(mpesaPollTimer); mpesaPollTimer = null; }

        if (data.verifying) {
          // Safaricom answers the status query on its own callback, so watch
          // the application until that lands.
          setManualStatus('#1565c0', '&#x23F3; ' + data.message);
          btn.innerHTML = '<i data-lucide="loader"></i> Verifying&hellip;';
          if (window.lucide) lucide.createIcons();
          pollManualVerification(data.reference, code);
          return;
        }

        // A C2B match settles the fee on the spot, so unlock the rest of the
        // form immediately rather than leaving it to the slow gate watch.
        if (data.payment_status === 'paid') {
          markPaid(data.reference);
          setManualStatus('#2e7d32', '&#10003; ' + data.message + ' Reference ' + data.reference + '.');
          btn.innerHTML = '<i data-lucide="check"></i> Verified';
          if (window.lucide) lucide.createIcons();
          return;
        }

        setManualStatus('#2e7d32', '&#10003; ' + data.message + ' Reference ' + data.reference + '.');
        btn.innerHTML = '<i data-lucide="check"></i> Recorded';
        if (window.lucide) lucide.createIcons();
      } catch (err) {
        console.error('[mpesa-manual]', err);
        setManualStatus('#c0392b', '&#x26A0; A network error occurred. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="check-circle-2"></i> Completed';
        if (window.lucide) lucide.createIcons();
      }
    }

    /**
     * Watch a manually entered code until Safaricom's Transaction Status
     * result lands. Uses its own timer so it cannot clash with the STK poll.
     */
    function manualVerifyDone(html, color, label) {
      if (manualVerifyTimer) { clearInterval(manualVerifyTimer); manualVerifyTimer = null; }
      setManualStatus(color, html);
      var btn = document.getElementById('manualPaidBtn');
      btn.innerHTML = label;
      if (window.lucide) lucide.createIcons();
    }

    function pollManualVerification(reference, code) {
      if (manualVerifyTimer) { clearInterval(manualVerifyTimer); manualVerifyTimer = null; }

      var elapsed = 0;

      manualVerifyTimer = setInterval(async function () {
        elapsed += 4;

        try {
          const res = await fetch('/applications/status/' + reference, {
            headers: { 'Accept': 'application/json' }
          });
          const data = await res.json();

          if (data.payment_status === 'paid') {
            markPaid(reference);
            manualVerifyDone(
              '&#10003; Payment of code ' + code + ' confirmed by M-Pesa. Reference ' + reference + '.',
              '#2e7d32',
              '<i data-lucide="check"></i> Verified'
            );
            return;
          }

          // Safaricom answered, but the code did not check out.
          if (data.payment_note) {
            manualVerifyDone(
              '&#x26A0; ' + data.payment_note + ' Reference ' + reference + '.',
              '#c0392b',
              '<i data-lucide="check-circle-2"></i> Completed'
            );
            document.getElementById('manualPaidBtn').disabled = false;
            return;
          }
        } catch (err) {
          console.error('[mpesa-manual] poll', err);
        }

        if (elapsed >= 120) {
          manualVerifyDone(
            '&#x23F3; Your payment is recorded and awaiting confirmation. Reference ' + reference + '.',
            '#1565c0',
            '<i data-lucide="check"></i> Recorded'
          );
        }
      }, 4000);
    }

    /* Ask Safaricom directly what became of the prompt. Used only when the
       callback is overdue -- the endpoint is rate limited, so this must never
       run on every poll tick. */
    async function stkQueryFallback() {
      try {
        const res = await fetch('/applications/stk-query', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          }
        });
        if (!res.ok) return null;
        const data = await res.json();
        return data.payment_status || null;
      } catch (err) {
        console.error('[mpesa] stk-query', err);
        return null;
      }
    }

    function pollMpesaStatus(reference) {
      // Daraja usually calls back within 30s; give the user up to 2 minutes.
      var elapsed = 0;
      mpesaPollTimer = setInterval(async function () {
        elapsed += 4;

        try {
          const res = await fetch('/applications/status/' + reference, {
            headers: { 'Accept': 'application/json' }
          });
          const data = await res.json();

          if (data.payment_status === 'paid') {
            clearInterval(mpesaPollTimer);
            mpesaPollTimer = null;
            markPaid(reference);
            setMpesaStatus('#2e7d32', '&#10003; Payment received. Reference ' + reference + '.');
            document.getElementById('mpesaPromptBtn').innerHTML = '<i data-lucide="check"></i> Paid';
            if (window.lucide) lucide.createIcons();
            return;
          }

          if (data.payment_status === 'awaiting_verification') {
            clearInterval(mpesaPollTimer);
            mpesaPollTimer = null;
            setMpesaStatus('#1565c0', '&#x23F3; Your payment is recorded and awaiting confirmation. Reference ' + reference + '.');
            return;
          }

          if (data.payment_status === 'failed') {
            clearInterval(mpesaPollTimer);
            mpesaPollTimer = null;
            setMpesaStatus('#c0392b', '&#x26A0; Payment failed or was cancelled. Tap the button to try again, or pay manually below.');
            resetMpesaButton();
            return;
          }
        } catch (err) {
          console.error('[mpesa] poll', err);
        }

        // A callback normally lands within 30s. Past that, ask Safaricom
        // rather than waiting out the clock on a webhook that may be lost.
        // Twice only: at 40s, and once more before giving up.
        if (elapsed === 40 || elapsed === 112) {
          var queried = await stkQueryFallback();

          if (queried === 'paid') {
            clearInterval(mpesaPollTimer);
            mpesaPollTimer = null;
            markPaid(reference);
            setMpesaStatus('#2e7d32', '&#10003; Payment received. Reference ' + reference + '.');
            document.getElementById('mpesaPromptBtn').innerHTML = '<i data-lucide="check"></i> Paid';
            if (window.lucide) lucide.createIcons();
            return;
          }

          if (queried === 'failed') {
            clearInterval(mpesaPollTimer);
            mpesaPollTimer = null;
            setMpesaStatus('#c0392b', '&#x26A0; Payment failed or was cancelled. Tap the button to try again, or pay manually below.');
            resetMpesaButton();
            return;
          }
        }

        if (elapsed >= 120) {
          // Hand over to the slow watch rather than stopping: a payment
          // confirmed at five minutes still updates this step in place.
          clearInterval(mpesaPollTimer);
          mpesaPollTimer = null;
          refreshPaymentUi(null);
          startPayGateWatch();
        }
      }, 4000);
    }

    function submitApplication() {
      for (var s = 1; s <= 7; s++) {
        var vfn = STEP_VALIDATORS[s];
        if (vfn && !vfn()) {
          document.getElementById('formIntro').style.display = 'none';
          document.getElementById('stepper').style.display = 'flex';
          document.getElementById('appGrid').style.display = 'grid';
          showStep(s);
          setTimeout(function () {
            var firstErr = document.getElementById('step' + s).querySelector('.field-error, .radio-group.error, .upload-zone.blank-error');
            if (firstErr && firstErr.scrollIntoView) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }, 150);
          alert('Please correct the highlighted fields (Step ' + s + ') before submitting.');
          return;
        }
      }
      saveApplication();
    }

    /* Every field the 7 steps collect, keyed the way the admin panel reads them. */
    function collectApplicationFields() {
      var textIds = [
        'f_name', 'f_dob', 'f_nationality', 'f_id', 'f_phone', 'f_email',
        'f_county', 'f_address', 'f_qual', 'f_school', 'f_year', 'f_grade',
        'f_addqual', 'f_school_sel', 'f_program', 'f_intake_month',
        'f_intake_year', 'f_campus', 'f_mpesa_phone',
        'f_room_type', 'f_acc_campus', 'f_acc_intake', 'f_acc_notes'
      ];

      var fields = {};
      textIds.forEach(function (id) {
        fields[id.replace(/^f_/, '')] = val(id);
      });

      ['gender', 'mode', 'accommodation'].forEach(function (name) {
        var checked = document.querySelector('input[name="' + name + '"]:checked');
        fields[name] = checked ? checked.value : '';
      });

      return fields;
    }

    async function saveApplication() {
      var btn = document.getElementById('submitBtn');
      var original = btn ? btn.innerHTML : null;

      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader"></i> Submitting&hellip;';
        if (window.lucide) lucide.createIcons();
      }

      var fields = collectApplicationFields();

      try {
        const response = await fetch('/applications/submit', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            name: fields.name,
            email: fields.email,
            phone: fields.phone,
            program: fields.program,
            fields: fields
          })
        });

        const data = await response.json();

        if (!response.ok) {
          var msg = data.message || 'We could not save your application. Please try again.';
          if (data.errors) {
            msg = Object.keys(data.errors).map(function (k) { return data.errors[k][0]; }).join('\n');
          }
          alert(msg);
          if (btn) { btn.disabled = false; btn.innerHTML = original; if (window.lucide) lucide.createIcons(); }
          return;
        }

        clearDraft();
        document.getElementById('appIdValue').textContent = data.reference;
        document.getElementById('successOverlay').classList.add('show');
        if (window.lucide) lucide.createIcons();
      } catch (err) {
        console.error('[apply]', err);
        alert('A network error occurred while submitting. Please try again.');
        if (btn) { btn.disabled = false; btn.innerHTML = original; if (window.lucide) lucide.createIcons(); }
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      if (window.lucide) lucide.createIcons();

      // Intro -> start form
      document.getElementById('introBeginBtn').addEventListener('click', function () {
        document.getElementById('formIntro').style.display = 'none';
        document.getElementById('stepper').style.display = 'flex';
        document.getElementById('appGrid').style.display = 'grid';
        showStep(1);
      });

      // Next / Previous / Edit navigation
      document.querySelectorAll('[data-next]').forEach(b => b.addEventListener('click', function () {
        var vfn = STEP_VALIDATORS[currentStep];
        if (vfn && !vfn()) { revealFirstError(currentStep); return; }
        showStep(parseInt(b.dataset.next));
      }));
      document.querySelectorAll('[data-prev]').forEach(b => b.addEventListener('click', () => showStep(parseInt(b.dataset.prev))));
      document.querySelectorAll('[data-goto]').forEach(b => b.addEventListener('click', () => showStep(parseInt(b.dataset.goto))));

      // Live-clear validation errors as the user types / changes values
      document.querySelectorAll('#appGrid input, #appGrid select, #appGrid textarea').forEach(function (el) {
        el.addEventListener('input', function () { clearElError(el); });
        el.addEventListener('change', function () { clearElError(el); });
      });

      // Upload zones
      document.querySelectorAll('.upload-zone').forEach(zone => {
        const input = zone.querySelector('input[type="file"]');
        if (!input) return;
        const label = zone.querySelector('.upload-label') || zone.querySelector('p');
        const hint = zone.querySelectorAll('p')[zone.querySelectorAll('p').length - 1];
        const defaultLabelHTML = label.innerHTML;
        const defaultHintHTML = hint.innerHTML;
        const acceptList = (input.accept || '').split(',').map(s => s.trim().toLowerCase()).filter(Boolean);
        const maxMbMatch = (hint.textContent || '').match(/Max\s*([\d.]+)\s*MB/i);
        const maxMb = maxMbMatch ? parseFloat(maxMbMatch[1]) : null;

        function resetZone() {
          zone.classList.remove('has-file', 'upload-error');
          label.innerHTML = defaultLabelHTML;
          hint.innerHTML = defaultHintHTML;
        }

        function showError(msg) {
          zone.classList.remove('has-file');
          zone.classList.add('upload-error');
          label.textContent = 'Upload failed';
          hint.innerHTML = '<span class="upload-error-msg">' + msg + '</span>';
        }

        function validFile(file) {
          if (acceptList.length) {
            const ext = '.' + file.name.split('.').pop().toLowerCase();
            if (!acceptList.includes(ext)) return 'Unsupported file type (' + ext + ')';
          }
          if (maxMb && file.size > maxMb * 1024 * 1024) return 'File exceeds ' + maxMb + 'MB limit';
          return null;
        }

        function showFiles(files) {
          for (const file of files) {
            const err = validFile(file);
            if (err) { showError(err); return false; }
          }
          zone.classList.remove('upload-error');
          zone.classList.add('has-file');
          const names = Array.from(files).map(f => f.name);
          label.innerHTML = files.length > 1
            ? '<span class="upload-filename">' + files.length + ' files selected</span>'
            : '<span class="upload-filename">' + names[0] + '</span>';
          hint.textContent = 'Click to replace or drag a new file';
          return true;
        }

        /* Send the file straight away rather than bundling everything into
           the final submit: each request stays under the per-file limit, the
           error names the file that is actually wrong, and an applicant who
           comes back tomorrow still has their documents. */
        async function uploadFiles(files) {
          const kind = zone.dataset.kind;
          if (!kind) return true;

          label.innerHTML = '<span class="upload-filename">Uploading&hellip;</span>';
          hint.textContent = files.length > 1 ? files.length + ' files' : files[0].name;

          for (const file of files) {
            const body = new FormData();
            body.append('kind', kind);
            body.append('file', file);

            try {
              const res = await fetch('/applications/documents', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: body
              });
              const data = await res.json();

              if (!res.ok) {
                showError((data.errors && data.errors.file && data.errors.file[0]) || data.error || 'Upload failed. Please try again.');
                return false;
              }

              uploadedDocs[kind] = (uploadedDocs[kind] || []).concat([data.id]);
            } catch (err) {
              console.error('[upload]', err);
              showError('Upload failed. Check your connection and try again.');
              return false;
            }
          }

          showFiles(files);
          saveDraft();
          return true;
        }

        input.addEventListener('change', async () => {
          if (input.files && input.files.length) {
            if (!showFiles(input.files)) { input.value = ''; return; }
            // A single-file zone replaces server-side, so forget the old id.
            if (!input.multiple) uploadedDocs[zone.dataset.kind] = [];
            if (!await uploadFiles(input.files)) input.value = '';
          } else {
            resetZone();
          }
        });

        zone.addEventListener('click', () => input.click());
        zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
        zone.addEventListener('drop', e => {
          e.preventDefault();
          zone.classList.remove('dragover');
          const files = e.dataTransfer.files;
          if (!files || !files.length) return;
          if (input.multiple) {
            input.files = files;
          } else {
            const dt = new DataTransfer();
            dt.items.add(files[0]);
            input.files = dt.files;
          }
          input.dispatchEvent(new Event('change', { bubbles: true }));
        });
      });

      // Navbar scroll shadow
      const nav = document.getElementById('mainNav');
      if (nav) window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 40));
    });

    // Back to top
    (function () {
      var btn = document.getElementById('backToTop');
      if (!btn) return;
      window.addEventListener('scroll', function () { btn.classList.toggle('visible', window.scrollY > 400); });
      btn.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
    })();
  </script>
  <script src="mobile-nav.js"></script>
  <script>
    (function () {
      const programs = {
        medical: [
          'Diploma in Perioperative Theatre Technology – Level 6',
          'Diploma in Mortuary Science – Level 6',
          'Diploma in Orthopaedic & Trauma Medicine – Level 6',
          'Diploma in Community Health Assistant – Level 6',
          'Certificate in Healthcare Support Services – Level 5 (CNA)',
          'Certificate in Caregiving – Level 4',
          'Certificate in Home-Based Care Support – Level 3',
          'Certificate in Homecare Management – Level 3',
          'Certificate in Homecare Management – Level 4',
          'Certificate in Community Health Assistant – Level 5',
          'Certified Nursing Assistant (CNA) Options',
          'Basic Life Support (BLS)',
          'Advanced Cardiac Life Support (ACLS)',
          'Pediatric Advanced Life Support (PALS)',
          'Heartsaver First Aid',
        ],
        hospitality: [
          'Certificate in Food & Beverage Production (Culinary Arts) – Level 3',
          'Certificate in Front Office Operations – Level 3',
          'Certificate in Housekeeping & Accommodation – Level 3',
          'Certificate in Homecare Management – Level 3',
          'Certificate in Homecare Management – Level 4',
        ],
        social: [
          'Diploma in Social Work & Community Development – Level 6',
          'Certificate in Office Administrator – Level 5',
          'Certificate in Office Assistant / Customer Service – Level 4',
        ],
        intl: [
          'AMCA (USA) – American Medical Certification Association',
          'SDC Canada – Skill Development Council',
          'ICDL Global – Ireland',
          'AHA – American Heart Association',
        ],
      };

      const schoolSel = document.getElementById('f_school_sel');
      const programSel = document.getElementById('f_program');

      schoolSel.addEventListener('change', function () {
        const key = this.value;
        programSel.innerHTML = '<option value="">Select Program</option>';
        if (key && programs[key]) {
          programs[key].forEach(function (name) {
            const opt = document.createElement('option');
            opt.value = name;
            opt.textContent = name;
            programSel.appendChild(opt);
          });
        }
        programSel.disabled = !key;
      });

      programSel.disabled = true;
    })();
  </script>
  <!-- ================= AUTOSAVE =================================
       Keeps the 7 steps in localStorage so a refresh, a dropped
       connection or a closed tab does not cost the applicant their
       answers. File inputs are deliberately absent: no browser lets a
       page re-attach a file it did not receive from the user.
       ============================================================ -->
  <script>
    (function () {
      var KEY = 'gocare_apply_draft_v1';
      var MAX_AGE_DAYS = 30;
      var saveTimer = null;

      function storage() {
        try {
          var s = window.localStorage;
          s.setItem('__gc_probe', '1');
          s.removeItem('__gc_probe');
          return s;
        } catch (e) {
          return null;           // private mode, or site data blocked
        }
      }

      /* Every value the form holds that a browser is allowed to restore. */
      function collectDraft() {
        var grid = document.getElementById('appGrid');
        if (!grid) return null;

        var fields = {};
        grid.querySelectorAll('input, select, textarea').forEach(function (el) {
          if (!el.id || el.type === 'file' || el.type === 'radio' || el.type === 'checkbox') return;
          if (el.value) fields[el.id] = el.value;
        });

        var radios = {};
        grid.querySelectorAll('input[type="radio"]:checked').forEach(function (el) {
          if (el.name) radios[el.name] = el.value;
        });

        return {
          v: 1,
          savedAt: Date.now(),
          step: (typeof currentStep === 'number' ? currentStep : 1),
          reference: (typeof mpesaReference !== 'undefined' ? mpesaReference : null),
          fields: fields,
          radios: radios
        };
      }

      window.saveDraft = function () {
        var s = storage();
        if (!s) return;
        var d = collectDraft();
        if (!d) return;
        try { s.setItem(KEY, JSON.stringify(d)); } catch (e) { /* quota */ }
      };

      window.clearDraft = function () {
        var s = storage();
        if (!s) return;
        try { s.removeItem(KEY); } catch (e) { /* ignore */ }
      };

      function readDraft() {
        var s = storage();
        if (!s) return null;
        var raw;
        try { raw = s.getItem(KEY); } catch (e) { return null; }
        if (!raw) return null;
        var d;
        try { d = JSON.parse(raw); } catch (e) { return null; }
        if (!d || d.v !== 1 || !d.savedAt) return null;
        if (Date.now() - d.savedAt > MAX_AGE_DAYS * 86400000) { window.clearDraft(); return null; }
        var filled = d.fields && Object.keys(d.fields).length;
        return filled ? d : null;
      }

      function scheduleSave() {
        if (saveTimer) clearTimeout(saveTimer);
        saveTimer = setTimeout(window.saveDraft, 400);
      }

      function describeAge(ms) {
        var mins = Math.floor((Date.now() - ms) / 60000);
        if (mins < 2) return 'a moment ago';
        if (mins < 60) return mins + ' minutes ago';
        var hours = Math.floor(mins / 60);
        if (hours < 24) return hours === 1 ? 'about an hour ago' : 'about ' + hours + ' hours ago';
        var days = Math.floor(hours / 24);
        return days === 1 ? 'yesterday' : days + ' days ago';
      }

      function restoreDraft(d) {
        var fields = d.fields || {};

        Object.keys(fields).forEach(function (id) {
          if (id === 'f_program') return;          // filled by the school dropdown below
          var el = document.getElementById(id);
          if (!el || el.type === 'file') return;
          el.value = fields[id];
          // The programme list is built from the school choice, so let that
          // handler run before we try to select a programme.
          if (id === 'f_school_sel') el.dispatchEvent(new Event('change', { bubbles: true }));
        });

        if (fields.f_program) {
          var prog = document.getElementById('f_program');
          if (prog) prog.value = fields.f_program;
        }

        Object.keys(d.radios || {}).forEach(function (name) {
          var v = d.radios[name];
          if (!v) return;
          var found = null;
          document.querySelectorAll('input[name="' + name + '"]').forEach(function (r) {
            if (r.value === v) found = r;
          });
          if (found) found.checked = true;
        });
        if (typeof toggleAccommodation === 'function') toggleAccommodation();

        if (d.reference && typeof mpesaReference !== 'undefined') mpesaReference = d.reference;

        document.getElementById('formIntro').style.display = 'none';
        document.getElementById('stepper').style.display = 'flex';
        document.getElementById('appGrid').style.display = 'grid';

        var step = parseInt(d.step, 10);
        if (!step || step < 1) step = 1;
        if (step > 7) step = 7;

        // Restoring must not drop the applicant past a step they never
        // completed. Walk up to the saved step and stop at the first one that
        // still fails; the validator marks up what is missing on the way, so
        // they land looking at the reason. Step 4 always stops a resume from
        // beyond it, because the uploads genuinely are gone.
        var target = step;
        for (var s = 1; s < step; s++) {
          var check = (typeof STEP_VALIDATORS !== 'undefined') && STEP_VALIDATORS[s];
          if (check && !check()) { target = s; break; }
        }
        showStep(target);

        // Past the upload step the applicant needs to know the files are gone.
        if (step > 4) {
          var notice = document.getElementById('restoreNotice');
          if (notice) notice.style.display = 'block';
        }
        if (window.lucide) lucide.createIcons();
      }

      document.addEventListener('DOMContentLoaded', function () {
        var grid = document.getElementById('appGrid');
        if (grid) {
          grid.addEventListener('input', scheduleSave);
          grid.addEventListener('change', scheduleSave);
        }

        var dismiss = document.getElementById('restoreNoticeDismiss');
        if (dismiss) dismiss.addEventListener('click', function () {
          document.getElementById('restoreNotice').style.display = 'none';
        });

        var draft = readDraft();
        if (!draft) return;

        var box = document.getElementById('resumeBox');
        var text = document.getElementById('resumeText');
        if (!box) return;

        if (text) {
          text.textContent = 'We kept everything you filled in on this device, saved '
            + describeAge(draft.savedAt) + ' at step ' + (draft.step || 1)
            + ' of 7. Pick up where you left off, or start over.';
        }
        box.style.display = 'block';
        if (window.lucide) lucide.createIcons();

        document.getElementById('resumeContinueBtn').addEventListener('click', function () {
          box.style.display = 'none';
          restoreDraft(draft);
        });
        document.getElementById('resumeFreshBtn').addEventListener('click', function () {
          window.clearDraft();
          box.style.display = 'none';
        });
      });
    })();
  </script>
  <script src="search-index.js" defer></script>
  <script id="gc-search-js" src="search.js" defer></script>
  <script src="accessibility.js" defer></script>
</body>
</html>

