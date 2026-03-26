<?php
require_once __DIR__ . '/includes/auth.php';
Auth::require(); // This will redirect to login.php if the user is not logged in
$user = Auth::user();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Enterprise CRM — Education Platform</title>
    <link
      href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Mono:wght@400;500&display=swap"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
      rel="stylesheet"
    />
    <style>
      :root {
        --bg: #0d0f14;
        --bg2: #13161e;
        --bg3: #1a1e28;
        --surface: #1e2230;
        --border: #2a2f42;
        --border2: #3a4060;
        --accent: #4f7cff;
        --accent2: #7c5cfc;
        --ag: rgba(79, 124, 255, 0.15);
        --green: #22d3a0;
        --orange: #f5924e;
        --red: #f0516c;
        --yellow: #f5c842;
        --purple: #a78bfa;
        --text: #e8ecf5;
        --text2: #8d95b0;
        --text3: #5a6180;
        --r: 12px;
        --r2: 8px;
        --font: "DM Sans", sans-serif;
        --mono: "DM Mono", monospace;
      }
      *,
      *::before,
      *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
      }
      html,
      body {
        height: 100%;
        background: var(--bg);
        color: var(--text);
        font-family: var(--font);
        font-size: 14px;
        line-height: 1.6;
      }
      ::-webkit-scrollbar {
        width: 5px;
        height: 5px;
      }
      ::-webkit-scrollbar-track {
        background: var(--bg2);
      }
      ::-webkit-scrollbar-thumb {
        background: var(--border2);
        border-radius: 99px;
      }

      /* ── LOGIN ── */
      #login-screen {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: var(--bg);
        position: relative;
      }
      #login-screen::before {
        content: "";
        position: fixed;
        inset: 0;
        background: radial-gradient(
          ellipse 70% 50% at 50% 0%,
          rgba(79, 124, 255, 0.1),
          transparent
        );
        pointer-events: none;
      }
      .lcard {
        width: 100%;
        max-width: 420px;
        background: var(--bg2);
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 40px;
        box-shadow: 0 24px 80px rgba(0, 0, 0, 0.5);
        position: relative;
        z-index: 1;
      }
      .llogo {
        display: flex;
        align-items: center;
        gap: 13px;
        margin-bottom: 30px;
        justify-content: center;
      }
      .lmark {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--accent), var(--accent2));
        display: grid;
        place-items: center;
        font-size: 18px;
        font-weight: 700;
        color: #fff;
        box-shadow: 0 0 24px rgba(79, 124, 255, 0.35);
        flex-shrink: 0;
      }
      .lname {
        font-size: 18px;
        font-weight: 700;
      }
      .lver {
        font-size: 10.5px;
        color: var(--text3);
      }
      .l-err {
        background: rgba(240, 81, 108, 0.1);
        border: 1px solid rgba(240, 81, 108, 0.25);
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 13px;
        color: var(--red);
        margin-bottom: 14px;
        display: none;
        align-items: center;
        gap: 8px;
      }
      .l-err.show {
        display: flex;
      }
      .demo-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 18px;
      }
      .dpill {
        padding: 4px 11px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 99px;
        font-size: 12px;
        color: var(--text2);
        cursor: pointer;
        transition: all 0.15s;
      }
      .dpill:hover {
        border-color: var(--accent);
        color: var(--accent);
      }

      /* ── APP LAYOUT ── */
      #app {
        display: flex;
        height: 100vh;
        overflow: hidden;
        flex-direction: row;
      }
      .sb {
        width: 230px;
        min-width: 230px;
        background: var(--bg2);
        border-right: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        transition: width 0.25s;
      }
      .sb-logo {
        padding: 17px 16px 13px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 10px;
      }
      .sb-mark {
        width: 33px;
        height: 33px;
        border-radius: 9px;
        background: linear-gradient(135deg, var(--accent), var(--accent2));
        display: grid;
        place-items: center;
        font-size: 14px;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
      }
      .sb-nm {
        font-size: 13px;
        font-weight: 600;
      }
      .sb-sub {
        font-size: 10px;
        color: var(--text3);
      }
      .sb-sec {
        padding: 15px 14px 3px;
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--text3);
      }
      .ni {
        margin: 1px 7px;
      }
      .nl {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 8px 11px;
        border-radius: var(--r2);
        color: var(--text2);
        font-size: 13px;
        font-weight: 450;
        cursor: pointer;
        transition: all 0.15s;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
      }
      .nl:hover {
        background: var(--surface);
        color: var(--text);
      }
      .nl.active {
        background: var(--ag);
        color: var(--accent);
        border-left: 2px solid var(--accent);
        padding-left: 9px;
      }
      .nl.hidden {
        display: none;
      }
      .nl i {
        font-size: 14.5px;
        width: 18px;
        text-align: center;
      }
      .nb {
        margin-left: auto;
        background: var(--accent);
        color: #fff;
        font-size: 10px;
        font-weight: 600;
        padding: 1px 7px;
        border-radius: 99px;
        min-width: 18px;
        text-align: center;
      }
      .sb-foot {
        margin-top: auto;
        padding: 13px 10px;
        border-top: 1px solid var(--border);
        flex-shrink: 0;
      }
      .uc {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 8px 10px;
        border-radius: var(--r2);
        background: var(--surface);
        margin-bottom: 7px;
      }
      .ua {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--accent), var(--accent2));
        display: grid;
        place-items: center;
        font-size: 12px;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
      }
      .role-tag {
        font-size: 10px;
        font-weight: 600;
        padding: 1px 7px;
        border-radius: 99px;
        background: var(--ag);
        color: var(--accent);
        margin-top: 1px;
        display: inline-block;
      }

      .main {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
      }
      .topbar {
        padding: 13px 24px;
        border-bottom: 1px solid var(--border);
        background: var(--bg2);
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
      }
      .tb-title {
        font-size: 15.5px;
        font-weight: 600;
      }
      .tb-sub {
        font-size: 11.5px;
        color: var(--text3);
        margin-top: 1px;
      }
      .tb-actions {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 7px;
      }
      .ca {
        flex: 1;
        overflow-y: auto;
        padding: 20px 24px;
      }

      /* ── STATS ── */
      .sg {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 18px;
      }
      .sc {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r);
        padding: 15px 17px;
        position: relative;
        overflow: hidden;
        transition:
          border-color 0.2s,
          transform 0.2s;
      }
      .sc:hover {
        border-color: var(--border2);
        transform: translateY(-1px);
      }
      .sc::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--ca, var(--accent));
      }
      .sc-icon {
        width: 33px;
        height: 33px;
        border-radius: 8px;
        background: var(--ci, var(--ag));
        display: grid;
        place-items: center;
        font-size: 15px;
        color: var(--ca, var(--accent));
        margin-bottom: 8px;
      }
      .sc-val {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: -0.5px;
        line-height: 1;
        margin-bottom: 2px;
      }
      .sc-lbl {
        font-size: 11.5px;
        color: var(--text3);
      }

      /* ── BUTTONS ── */
      .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: var(--r2);
        font-family: var(--font);
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        transition: all 0.15s;
        white-space: nowrap;
        text-decoration: none;
      }
      .bp {
        background: var(--accent);
        color: #fff;
      }
      .bp:hover {
        background: #3d6de8;
        box-shadow: 0 4px 16px rgba(79, 124, 255, 0.3);
      }
      .bg {
        background: transparent;
        color: var(--text2);
        border: 1px solid var(--border);
      }
      .bg:hover {
        background: var(--surface);
        color: var(--text);
        border-color: var(--border2);
      }
      .bs {
        background: var(--green);
        color: #fff;
      }
      .bs:hover {
        background: #1bb88a;
      }
      .bd {
        background: transparent;
        color: var(--red);
        border: 1px solid var(--red);
      }
      .bd:hover {
        background: var(--red);
        color: #fff;
      }
      .bw {
        background: var(--yellow);
        color: #111;
      }
      .bsm {
        padding: 5px 10px;
        font-size: 12px;
      }
      .bic {
        padding: 7px;
      }
      .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
      }

      /* ── TOOLBAR ── */
      .tb {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 13px;
        flex-wrap: wrap;
      }
      .sbw {
        position: relative;
        flex: 1;
        min-width: 180px;
      }
      .sbw i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text3);
        font-size: 13px;
      }
      .sbw input {
        width: 100%;
        padding: 8px 11px 8px 32px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r2);
        color: var(--text);
        font-family: var(--font);
        font-size: 13px;
        outline: none;
        transition: border-color 0.15s;
      }
      .sbw input:focus {
        border-color: var(--accent);
      }
      .sbw input::placeholder {
        color: var(--text3);
      }
      .fsel {
        padding: 8px 27px 8px 11px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r2);
        color: var(--text);
        font-family: var(--font);
        font-size: 13px;
        outline: none;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%235a6180'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 9px center;
        transition: border-color 0.15s;
      }
      .fsel:focus {
        border-color: var(--accent);
      }

      /* ── TABLE ── */
      .tw {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r);
        overflow: hidden;
      }
      .ct {
        width: 100%;
        border-collapse: collapse;
      }
      .ct thead tr {
        background: var(--bg3);
        border-bottom: 1px solid var(--border);
      }
      .ct thead th {
        padding: 10px 14px;
        text-align: left;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--text3);
      }
      .ct tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background 0.1s;
      }
      .ct tbody tr:last-child {
        border-bottom: none;
      }
      .ct tbody tr:hover {
        background: var(--bg3);
      }
      .ct td {
        padding: 12px 14px;
        vertical-align: middle;
        font-size: 13.5px;
      }

      /* ── BADGES ── */
      .bx {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 9px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 600;
      }
      .bx::before {
        content: "";
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
      }
      .active,
      .approved,
      .paid,
      .signed,
      .complete {
        background: rgba(34, 211, 160, 0.12) !important;
        color: var(--green) !important;
      }
      .draft,
      .pending,
      .open {
        background: rgba(245, 202, 66, 0.12) !important;
        color: var(--yellow) !important;
      }
      .in_progress,
      .processing,
      .sent,
      .uploaded,
      .partial {
        background: rgba(79, 124, 255, 0.12) !important;
        color: var(--accent) !important;
      }
      .archived,
      .inactive,
      .cancelled,
      .rejected,
      .failed,
      .refunded {
        background: rgba(90, 97, 128, 0.15) !important;
        color: var(--text3) !important;
      }
      .completed {
        background: rgba(34, 211, 160, 0.12) !important;
        color: var(--green) !important;
      }
      .rescheduled {
        background: rgba(245, 146, 78, 0.12) !important;
        color: var(--orange) !important;
      }
      .recorded {
        background: rgba(124, 92, 252, 0.12) !important;
        color: var(--accent2) !important;
      }
      .live {
        background: rgba(240, 81, 108, 0.12) !important;
        color: var(--red) !important;
      }
      .not_decided {
        background: rgba(90, 97, 128, 0.15) !important;
        color: var(--text3) !important;
      }
      .beginner {
        background: rgba(34, 211, 160, 0.1) !important;
        color: var(--green) !important;
      }
      .intermediate {
        background: rgba(245, 146, 78, 0.1) !important;
        color: var(--orange) !important;
      }
      .advanced {
        background: rgba(240, 81, 108, 0.1) !important;
        color: var(--red) !important;
      }
      .urgent {
        background: rgba(240, 81, 108, 0.15) !important;
        color: var(--red) !important;
      }
      .high {
        background: rgba(245, 146, 78, 0.12) !important;
        color: var(--orange) !important;
      }
      .medium {
        background: rgba(79, 124, 255, 0.12) !important;
        color: var(--accent) !important;
      }
      .low {
        background: rgba(90, 97, 128, 0.15) !important;
        color: var(--text3) !important;
      }

      /* ── CARDS ── */
      .cg {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
        gap: 13px;
      }
      .cc {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r);
        padding: 17px;
        cursor: pointer;
        transition:
          border-color 0.2s,
          transform 0.2s,
          box-shadow 0.2s;
        position: relative;
        overflow: hidden;
      }
      .cc:hover {
        border-color: var(--accent);
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(79, 124, 255, 0.1);
      }
      .cc-stripe {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--accent), var(--accent2));
      }

      /* ── PROGRESS ── */
      .pb-bg {
        background: var(--bg3);
        border-radius: 99px;
        height: 5px;
        overflow: hidden;
        margin-top: 3px;
      }
      .pb-fill {
        height: 100%;
        border-radius: 99px;
        background: linear-gradient(90deg, var(--accent), var(--green));
        transition: width 0.5s;
      }
      .pb-lbl {
        font-size: 11px;
        color: var(--text3);
        display: flex;
        justify-content: space-between;
        margin-bottom: 2px;
      }

      /* ── PROGRESS 3-PART ── */
      .prog3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 6px;
        margin: 9px 0;
      }
      .p3seg {
        background: var(--bg3);
        border-radius: 8px;
        padding: 8px;
        text-align: center;
      }
      .p3v {
        font-size: 15px;
        font-weight: 700;
        line-height: 1;
      }
      .p3l {
        font-size: 9.5px;
        color: var(--text3);
        margin-top: 2px;
      }

      /* ── MODAL ── */
      .mo {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.65);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 20px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s;
      }
      .mo.open {
        opacity: 1;
        pointer-events: all;
      }
      .mb {
        background: var(--bg2);
        border: 1px solid var(--border2);
        border-radius: 15px;
        width: 100%;
        max-width: 540px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 24px 80px rgba(0, 0, 0, 0.6);
        transform: scale(0.96) translateY(10px);
        transition: transform 0.2s;
      }
      .mo.open .mb {
        transform: scale(1) translateY(0);
      }
      .mb.lg {
        max-width: 660px;
      }
      .mh {
        padding: 17px 21px 13px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 11px;
      }
      .mhi {
        width: 33px;
        height: 33px;
        border-radius: 8px;
        background: var(--ag);
        display: grid;
        place-items: center;
        font-size: 15px;
        color: var(--accent);
        flex-shrink: 0;
      }
      .mt {
        font-size: 15px;
        font-weight: 600;
      }
      .ms {
        font-size: 11.5px;
        color: var(--text3);
      }
      .mc {
        margin-left: auto;
        background: none;
        border: none;
        color: var(--text3);
        font-size: 17px;
        cursor: pointer;
        padding: 4px;
        border-radius: 6px;
        transition: all 0.15s;
      }
      .mc:hover {
        color: var(--text);
        background: var(--surface);
      }
      .mbody {
        padding: 17px 21px;
      }
      .mfoot {
        padding: 13px 21px;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
        gap: 8px;
      }

      /* ── FORMS ── */
      .fg {
        margin-bottom: 13px;
      }
      .fl {
        display: block;
        font-size: 12px;
        font-weight: 550;
        color: var(--text2);
        margin-bottom: 5px;
        letter-spacing: 0.02em;
      }
      .fl span {
        color: var(--red);
        margin-left: 2px;
      }
      .fi,
      .fsi,
      .fta {
        width: 100%;
        padding: 8px 12px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r2);
        color: var(--text);
        font-family: var(--font);
        font-size: 13px;
        outline: none;
        transition:
          border-color 0.15s,
          box-shadow 0.15s;
      }
      .fi:focus,
      .fsi:focus,
      .fta:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(79, 124, 255, 0.1);
      }
      .fi::placeholder,
      .fta::placeholder {
        color: var(--text3);
      }
      .fta {
        resize: vertical;
        min-height: 70px;
      }
      .fsi {
        appearance: none;
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%235a6180'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 11px center;
        padding-right: 30px;
      }
      .fr {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
      }
      .fhint {
        font-size: 11px;
        color: var(--text3);
        margin-top: 3px;
      }
      .rs-box {
        background: rgba(245, 202, 66, 0.07);
        border: 1px solid rgba(245, 202, 66, 0.2);
        border-radius: 8px;
        padding: 12px;
        margin-top: 10px;
      }

      /* ── TABS ── */
      .tbar {
        display: flex;
        border-bottom: 1px solid var(--border);
        margin-bottom: 15px;
        flex-wrap: wrap;
      }
      .tbtn {
        padding: 8px 14px;
        background: none;
        border: none;
        color: var(--text3);
        font-family: var(--font);
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
        transition: all 0.15s;
        white-space: nowrap;
      }
      .tbtn:hover {
        color: var(--text);
      }
      .tbtn.active {
        color: var(--accent);
        border-bottom-color: var(--accent);
      }
      .tp {
        display: none;
      }
      .tp.active {
        display: block;
      }

      /* ── TOPIC ITEM ── */
      .ti {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r);
        padding: 12px 14px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 11px;
        transition:
          border-color 0.15s,
          background 0.15s;
      }
      .ti:hover {
        border-color: var(--border2);
        background: var(--bg3);
      }
      .tnum {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        background: var(--bg3);
        border: 1px solid var(--border);
        display: grid;
        place-items: center;
        font-family: var(--mono);
        font-size: 11px;
        color: var(--text3);
        flex-shrink: 0;
      }
      .tinfo {
        flex: 1;
        min-width: 0;
      }
      .ttitle {
        font-size: 13.5px;
        font-weight: 550;
        margin-bottom: 2px;
      }
      .tdesc {
        font-size: 12px;
        color: var(--text3);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
      .tmeta {
        display: flex;
        align-items: center;
        gap: 7px;
        flex-shrink: 0;
        flex-wrap: wrap;
      }
      .tact {
        display: flex;
        gap: 5px;
        flex-shrink: 0;
      }

      /* ── WORKFLOW ── */
      .wft {
        display: flex;
        flex-direction: column;
      }
      .wfs {
        display: flex;
        align-items: flex-start;
        gap: 13px;
        padding: 11px 0;
        position: relative;
      }
      .wfs:not(:last-child)::after {
        content: "";
        position: absolute;
        left: 13px;
        top: 35px;
        bottom: -11px;
        width: 2px;
        background: var(--border);
      }
      .wfd {
        width: 27px;
        height: 27px;
        border-radius: 50%;
        border: 2px solid var(--border);
        display: grid;
        place-items: center;
        flex-shrink: 0;
        transition: all 0.2s;
        cursor: pointer;
        background: var(--bg3);
        z-index: 1;
      }
      .wfd.done {
        background: var(--green);
        border-color: var(--green);
        color: #fff;
      }
      .wfd:not(.done) {
        color: var(--text3);
        font-size: 12px;
      }
      .wfd:hover {
        border-color: var(--accent);
      }
      .wfi {
        flex: 1;
        padding-top: 3px;
      }
      .wfl {
        font-size: 13px;
        font-weight: 500;
      }
      .wfm {
        font-size: 11.5px;
        color: var(--text3);
        margin-top: 1px;
      }

      /* ── DASHBOARD ── */
      .dr {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 13px;
        margin-bottom: 13px;
      }
      .dc {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r);
        padding: 17px 18px;
      }
      .dct {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: var(--text3);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
      }
      .bar-r {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 8px;
      }
      .bar-l {
        width: 110px;
        color: var(--text2);
        font-size: 11.5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex-shrink: 0;
      }
      .bar-t {
        flex: 1;
        background: var(--bg3);
        border-radius: 99px;
        height: 6px;
        overflow: hidden;
      }
      .bar-f {
        height: 100%;
        border-radius: 99px;
        background: linear-gradient(90deg, var(--accent), var(--accent2));
        transition: width 0.6s;
      }
      .bar-v {
        width: 34px;
        text-align: right;
        color: var(--text3);
        font-size: 11.5px;
        flex-shrink: 0;
      }
      .upi {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 7px 0;
        border-bottom: 1px solid var(--border);
      }
      .upi:last-child {
        border-bottom: none;
      }

      /* ── DETAIL HEADER ── */
      .dh {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r);
        padding: 18px 21px;
        margin-bottom: 16px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
      }
      .dhicon {
        width: 44px;
        height: 44px;
        border-radius: 11px;
        background: linear-gradient(135deg, var(--accent), var(--accent2));
        display: grid;
        place-items: center;
        font-size: 19px;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 4px 16px rgba(79, 124, 255, 0.25);
      }
      .dhtitle {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 3px;
      }
      .dhmeta {
        display: flex;
        flex-wrap: wrap;
        gap: 11px;
        margin-top: 6px;
      }
      .dhmeta span {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 12.5px;
        color: var(--text2);
      }
      .dhmeta i {
        color: var(--text3);
      }

      /* ── EMPTY ── */
      .es {
        text-align: center;
        padding: 46px 20px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r);
      }
      .es-i {
        font-size: 34px;
        color: var(--text3);
        margin-bottom: 11px;
      }
      .es-t {
        font-size: 14.5px;
        font-weight: 600;
        margin-bottom: 5px;
      }
      .es-s {
        font-size: 13px;
        color: var(--text3);
        margin-bottom: 16px;
      }

      /* ── BREADCRUMB ── */
      .bc {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 14px;
        font-size: 12.5px;
        color: var(--text3);
      }
      .bc a {
        color: var(--accent);
        text-decoration: none;
        cursor: pointer;
      }
      .bc a:hover {
        text-decoration: underline;
      }
      .bc i {
        font-size: 10px;
      }

      /* ── TOAST ── */
      .tw-w {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 7px;
      }
      .toast {
        background: var(--surface);
        border: 1px solid var(--border2);
        border-radius: var(--r2);
        padding: 10px 14px;
        display: flex;
        align-items: center;
        gap: 9px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.4);
        min-width: 230px;
        max-width: 320px;
        animation: tin 0.2s ease;
      }
      .toast.suc {
        border-left: 3px solid var(--green);
      }
      .toast.err {
        border-left: 3px solid var(--red);
      }
      .toast.inf {
        border-left: 3px solid var(--accent);
      }
      .toast.suc i {
        color: var(--green);
      }
      .toast.err i {
        color: var(--red);
      }
      .toast.inf i {
        color: var(--accent);
      }
      .toast span {
        font-size: 13px;
        flex: 1;
      }
      @keyframes tin {
        from {
          opacity: 0;
          transform: translateX(15px);
        }
        to {
          opacity: 1;
          transform: translateX(0);
        }
      }

      /* ── TASK ITEM ── */
      .task-item {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r);
        padding: 13px 15px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: border-color 0.15s;
      }
      .task-item:hover {
        border-color: var(--border2);
      }
      .tpbar {
        width: 3px;
        height: 36px;
        border-radius: 99px;
        flex-shrink: 0;
      }
      .tpbar.urgent {
        background: var(--red);
      }
      .tpbar.high {
        background: var(--orange);
      }
      .tpbar.medium {
        background: var(--accent);
      }
      .tpbar.low {
        background: var(--text3);
      }

      /* ── MATERIAL TYPE ── */
      .mtype {
        display: inline-flex;
        padding: 2px 9px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        background: rgba(167, 139, 250, 0.12);
        color: var(--purple);
      }

      /* ── VIEW TOGGLE ── */
      .vt {
        display: flex;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r2);
        overflow: hidden;
      }
      .vb {
        padding: 6px 10px;
        background: none;
        border: none;
        color: var(--text3);
        cursor: pointer;
        font-size: 14px;
        transition: all 0.15s;
      }
      .vb.active {
        background: var(--accent);
        color: #fff;
      }

      /* ── MISC ── */
      .mono {
        font-family: var(--mono);
      }
      .tx-a {
        color: var(--accent);
      }
      .tx-g {
        color: var(--green);
      }
      .tx-r {
        color: var(--red);
      }
      .tx-y {
        color: var(--yellow);
      }
      .tx-m {
        color: var(--text3);
      }
      .divider {
        border: none;
        border-top: 1px solid var(--border);
        margin: 14px 0;
      }
      .sec-t {
        font-size: 11.5px;
        font-weight: 600;
        color: var(--text2);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 9px;
      }
      .ig {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 12px;
      }
      .ig label {
        font-size: 11px;
        color: var(--text3);
        display: block;
        margin-bottom: 1px;
      }
      .ig span {
        font-size: 13.5px;
      }
      .spin {
        width: 17px;
        height: 17px;
        border: 2px solid var(--border);
        border-top-color: var(--accent);
        border-radius: 50%;
        animation: sp 0.6s linear infinite;
        display: inline-block;
      }
      @keyframes sp {
        to {
          transform: rotate(360deg);
        }
      }

      @media (max-width: 900px) {
        .sg {
          grid-template-columns: repeat(2, 1fr);
        }
        .sb {
          width: 58px;
          min-width: 58px;
        }
        .sb-nm,
        .nl span,
        .sb-sec,
        .sb-foot .u-name,
        .sb-foot .u-role,
        .sb-foot .role-tag {
          display: none;
        }
        .nl {
          justify-content: center;
          padding: 10px;
        }
        .nb {
          display: none;
        }
        .uc {
          justify-content: center;
          padding: 7px;
        }
        .dr {
          grid-template-columns: 1fr;
        }
      }
      @media (max-width: 600px) {
        .sg {
          grid-template-columns: 1fr 1fr;
        }
        .ca {
          padding: 14px;
        }
        .fr {
          grid-template-columns: 1fr;
        }
      }
    </style>
  </head>
  <body>
    <!-- ═══ APP ═══ -->
    <div id="app">
      <aside class="sb">
        <div class="sb-logo">
          <div class="sb-mark">E</div>
          <div>
            <div class="sb-nm">Enterprise CRM</div>
            <div class="sb-sub">v3.0</div>
          </div>
        </div>
        <div class="sb-sec">Overview</div>
        <div class="ni">
          <button class="nl" id="nav-dashboard" onclick="go('dashboard')">
            <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
          </button>
        </div>
        <div class="ni">
          <button class="nl" id="nav-tasks" onclick="go('tasks')">
            <i class="bi bi-check2-square"></i><span>Tasks</span
            ><span class="nb" id="badge-tasks">—</span>
          </button>
        </div>
        <div class="sb-sec">Academics</div>
        <div class="ni">
          <button class="nl" id="nav-courses" onclick="go('courses')">
            <i class="bi bi-collection-play-fill"></i><span>Courses</span
            ><span class="nb" id="badge-c">—</span>
          </button>
        </div>
        <div class="ni">
          <button class="nl" id="nav-workflow" onclick="go('workflow')">
            <i class="bi bi-diagram-3-fill"></i><span>Workflow</span>
          </button>
        </div>
        <div class="ni">
          <button class="nl" id="nav-materials" onclick="go('materials')">
            <i class="bi bi-folder2-open"></i><span>Materials</span>
          </button>
        </div>
        <div class="sb-sec">Finance</div>
        <div class="ni">
          <button class="nl" id="nav-contracts" onclick="go('contracts')">
            <i class="bi bi-file-earmark-text-fill"></i><span>Contracts</span>
          </button>
        </div>
        <div class="ni">
          <button class="nl" id="nav-payments" onclick="go('payments')">
            <i class="bi bi-credit-card-2-front-fill"></i><span>Payments</span>
          </button>
        </div>
        <div class="sb-sec">People</div>
        <div class="ni">
          <button class="nl" id="nav-faculty" onclick="go('faculty')">
            <i class="bi bi-person-badge-fill"></i><span>Faculty</span>
          </button>
        </div>
        <div class="ni">
          <button class="nl hidden" id="nav-users" onclick="go('users')">
            <i class="bi bi-people-fill"></i><span>Users</span>
          </button>
        </div>
        <div class="sb-foot">
          <div class="uc">
            <div class="ua" id="sb-av">A</div>
            <div>
              <div
                class="u-name"
                style="font-size: 12.5px; font-weight: 500"
                id="sb-name"
              >
                —
              </div>
              <div class="u-role role-tag" id="sb-role">—</div>
            </div>
          </div>
          <button
            class="btn bg bsm"
            style="width: 100%; justify-content: center"
            onclick="doLogout()"
          >
            <i class="bi bi-box-arrow-right"></i> <span>Logout</span>
          </button>
        </div>
      </aside>
      <div class="main">
        <div class="topbar">
          <div>
            <div class="tb-title" id="tb-title">Dashboard</div>
            <div class="tb-sub" id="tb-sub">Welcome back</div>
          </div>
          <div class="tb-actions" id="tb-actions"></div>
        </div>
        <div class="ca" id="content"></div>
      </div>
    </div>

    <!-- ─ Universal Modal ─ -->
    <div class="mo" id="M" onclick="if (event.target === this) CM();">
      <div class="mb" id="M-box">
        <div class="mh">
          <div class="mhi" id="M-icon"><i class="bi bi-pencil"></i></div>
          <div>
            <div class="mt" id="M-title">Form</div>
            <div class="ms" id="M-sub"></div>
          </div>
          <button class="mc" onclick="CM()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="mbody" id="M-body"></div>
        <div class="mfoot" id="M-foot">
          <button class="btn bg" onclick="CM()">Cancel</button
          ><button class="btn bp" id="M-save">Save</button>
        </div>
      </div>
    </div>
    <!-- ─ Delete Modal ─ -->
    <div class="mo" id="DM" onclick="if (event.target === this) CDM();">
      <div class="mb" style="max-width: 390px">
        <div class="mh">
          <div class="mhi" style="background: rgba(240, 81, 108, 0.12)">
            <i class="bi bi-trash3" style="color: var(--red)"></i>
          </div>
          <div>
            <div class="mt">Confirm Delete</div>
            <div class="ms" id="DM-msg">Cannot be undone.</div>
          </div>
          <button class="mc" onclick="CDM()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="mfoot">
          <button class="btn bg" onclick="CDM()">Cancel</button
          ><button class="btn bd" onclick="XDM()">
            <i class="bi bi-trash3"></i> Delete
          </button>
        </div>
      </div>
    </div>
    <div class="tw-w" id="TW"></div>

    <script>
      // ═══════════════════════════════════════════════════════════════
      // DEMO USERS — mirrors DB seed
      // ═══════════════════════════════════════════════════════════════
      const DEMO_USERS = [
        {
          id: 1,
          name: "Dr. Owner",
          email: "owner@crm.com",
          pw: "password",
          role: "owner",
        },
        {
          id: 2,
          name: "Admin User",
          email: "admin@crm.com",
          pw: "password",
          role: "admin",
        },
        {
          id: 3,
          name: "Accounts Manager",
          email: "accounts@crm.com",
          pw: "password",
          role: "accounts",
        },
        {
          id: 4,
          name: "Sarah Coordinator",
          email: "sarah@crm.com",
          pw: "password",
          role: "coordinator",
        },
        {
          id: 5,
          name: "John Coordinator",
          email: "john@crm.com",
          pw: "password",
          role: "coordinator",
        },
      ];

      // ═══════════════════════════════════════════════════════════════
      // DATA STORE
      // ═══════════════════════════════════════════════════════════════
      const MAT = {
        osce: "OSCE",
        mcq: "MCQ",
        true_false: "True & False",
        dicom_long_case: "DICOM Long Case",
        dicom_short_case: "DICOM Short Case",
        spotters: "Spotters",
      };

      const D = {
        faculty: [
          {
            id: 1,
            name: "Dr. Priya Sharma",
            mobile: "+91-98765-10001",
            email: "priya@faculty.com",
            city: "Mumbai",
            state: "Maharashtra",
            country: "India",
            designation: "Associate Professor",
            status: "active",
          },
          {
            id: 2,
            name: "Prof. Rahul Mehta",
            mobile: "+91-98765-10002",
            email: "rahul@faculty.com",
            city: "Delhi",
            state: "Delhi",
            country: "India",
            designation: "Professor",
            status: "active",
          },
          {
            id: 3,
            name: "Dr. Anita Desai",
            mobile: "+91-98765-10003",
            email: "anita@faculty.com",
            city: "Ahmedabad",
            state: "Gujarat",
            country: "India",
            designation: "Senior Lecturer",
            status: "active",
          },
          {
            id: 4,
            name: "Dr. Vikram Nair",
            mobile: "+91-98765-10004",
            email: "vikram@faculty.com",
            city: "Bengaluru",
            state: "Karnataka",
            country: "India",
            designation: "Assistant Professor",
            status: "active",
          },
          {
            id: 5,
            name: "Prof. Sunita Rao",
            mobile: "+91-98765-10005",
            email: "sunita@faculty.com",
            city: "Hyderabad",
            state: "Telangana",
            country: "India",
            designation: "Professor",
            status: "active",
          },
        ],
        courses: [
          {
            id: 1,
            num: "CRS-2025-001",
            title: "Full Stack Web Development",
            desc: "Comprehensive full stack covering HTML, CSS, JS, PHP and MySQL.",
            cat: "Technology",
            level: "beginner",
            status: "active",
            coord: 4,
          },
          {
            id: 2,
            num: "CRS-2025-002",
            title: "Data Science with Python",
            desc: "Data analysis, visualization and machine learning using Python.",
            cat: "Data Science",
            level: "intermediate",
            status: "active",
            coord: 4,
          },
          {
            id: 3,
            num: "CRS-2025-003",
            title: "Digital Marketing Mastery",
            desc: "SEO, SEM, social media and content marketing strategy.",
            cat: "Marketing",
            level: "beginner",
            status: "active",
            coord: 5,
          },
          {
            id: 4,
            num: "CRS-2025-004",
            title: "Advanced PHP & Laravel",
            desc: "PHP 8 and Laravel framework for enterprise applications.",
            cat: "Technology",
            level: "advanced",
            status: "draft",
            coord: 5,
          },
          {
            id: 5,
            num: "CRS-2025-005",
            title: "UI/UX Design Fundamentals",
            desc: "UX/UI design using Figma, design thinking and prototyping.",
            cat: "Design",
            level: "beginner",
            status: "draft",
            coord: 4,
          },
        ],
        topics: [
          {
            id: 1,
            cid: 1,
            title: "HTML5 & Semantic Markup",
            desc: "Introduction to HTML5 elements, forms and accessibility.",
            type: "recorded",
            ord: 1,
            dur: 90,
            fid: 1,
            status: "completed",
            sched: "2025-01-10T10:00",
            link: null,
            notes: null,
            rReason: null,
            rDate: null,
          },
          {
            id: 2,
            cid: 1,
            title: "CSS3 & Flexbox Layout",
            desc: "CSS fundamentals, Flexbox, Grid and responsive design.",
            type: "recorded",
            ord: 2,
            dur: 120,
            fid: 1,
            status: "completed",
            sched: "2025-01-17T10:00",
            link: null,
            notes: null,
            rReason: null,
            rDate: null,
          },
          {
            id: 3,
            cid: 1,
            title: "JavaScript Essentials",
            desc: "Core JavaScript: DOM, events, ES6 features.",
            type: "live",
            ord: 3,
            dur: 90,
            fid: 2,
            status: "completed",
            sched: "2025-01-24T11:00",
            link: "https://meet.google.com/abc-defg",
            notes: null,
            rReason: null,
            rDate: null,
          },
          {
            id: 4,
            cid: 1,
            title: "Bootstrap 5 Framework",
            desc: "Responsive UIs with Bootstrap 5 components.",
            type: "recorded",
            ord: 4,
            dur: 60,
            fid: 1,
            status: "in_progress",
            sched: "2025-02-01T10:00",
            link: null,
            notes: null,
            rReason: null,
            rDate: null,
          },
          {
            id: 5,
            cid: 1,
            title: "PHP Fundamentals",
            desc: "PHP 8 syntax, arrays, functions and OOP basics.",
            type: "live",
            ord: 5,
            dur: 120,
            fid: 2,
            status: "pending",
            sched: "2025-02-10T11:00",
            link: "https://zoom.us/j/12345",
            notes: "Students must have XAMPP",
            rReason: null,
            rDate: null,
          },
          {
            id: 6,
            cid: 1,
            title: "MySQL Database Design",
            desc: "Relational DB design, SQL queries and joins.",
            type: "recorded",
            ord: 6,
            dur: 90,
            fid: 3,
            status: "pending",
            sched: "2025-02-17T10:00",
            link: null,
            notes: null,
            rReason: null,
            rDate: null,
          },
          {
            id: 7,
            cid: 1,
            title: "RESTful API Development",
            desc: "Building REST APIs with PHP.",
            type: "not_decided",
            ord: 7,
            dur: 0,
            fid: null,
            status: "pending",
            sched: null,
            link: null,
            notes: null,
            rReason: null,
            rDate: null,
          },
          {
            id: 8,
            cid: 1,
            title: "Final Project & Deployment",
            desc: "Capstone project and live deployment.",
            type: "not_decided",
            ord: 8,
            dur: 0,
            fid: null,
            status: "pending",
            sched: null,
            link: null,
            notes: null,
            rReason: null,
            rDate: null,
          },
          {
            id: 9,
            cid: 2,
            title: "Python for Data Science",
            desc: "Python basics, NumPy and scientific computing.",
            type: "recorded",
            ord: 1,
            dur: 90,
            fid: 3,
            status: "completed",
            sched: "2025-01-08T09:00",
            link: null,
            notes: null,
            rReason: null,
            rDate: null,
          },
          {
            id: 10,
            cid: 2,
            title: "Pandas & Data Wrangling",
            desc: "Data manipulation and cleaning with Pandas.",
            type: "recorded",
            ord: 2,
            dur: 120,
            fid: 3,
            status: "completed",
            sched: "2025-01-15T09:00",
            link: null,
            notes: null,
            rReason: null,
            rDate: null,
          },
          {
            id: 11,
            cid: 2,
            title: "Data Visualization",
            desc: "Charts with Matplotlib, Seaborn and Plotly.",
            type: "live",
            ord: 3,
            dur: 90,
            fid: 3,
            status: "in_progress",
            sched: "2025-01-22T09:00",
            link: null,
            notes: null,
            rReason: null,
            rDate: null,
          },
          {
            id: 12,
            cid: 2,
            title: "Statistics & Probability",
            desc: "Descriptive stats, distributions, hypothesis testing.",
            type: "recorded",
            ord: 4,
            dur: 120,
            fid: 1,
            status: "pending",
            sched: "2025-01-29T09:00",
            link: null,
            notes: null,
            rReason: null,
            rDate: null,
          },
          {
            id: 13,
            cid: 2,
            title: "Machine Learning Basics",
            desc: "Supervised learning with Scikit-learn.",
            type: "not_decided",
            ord: 5,
            dur: 0,
            fid: null,
            status: "pending",
            sched: null,
            link: null,
            notes: null,
            rReason: null,
            rDate: null,
          },
          {
            id: 14,
            cid: 3,
            title: "SEO Fundamentals",
            desc: "On/off-page SEO and keyword research.",
            type: "recorded",
            ord: 1,
            dur: 90,
            fid: 2,
            status: "completed",
            sched: "2025-01-06T14:00",
            link: null,
            notes: null,
            rReason: null,
            rDate: null,
          },
          {
            id: 15,
            cid: 3,
            title: "Google Ads & SEM",
            desc: "Setting up and optimising Google Ads.",
            type: "live",
            ord: 2,
            dur: 120,
            fid: 2,
            status: "completed",
            sched: "2025-01-13T14:00",
            link: null,
            notes: null,
            rReason: null,
            rDate: null,
          },
          {
            id: 16,
            cid: 3,
            title: "Social Media Marketing",
            desc: "Facebook, Instagram, LinkedIn, Twitter strategy.",
            type: "recorded",
            ord: 3,
            dur: 90,
            fid: 3,
            status: "in_progress",
            sched: "2025-01-20T14:00",
            link: null,
            notes: null,
            rReason: null,
            rDate: null,
          },
          {
            id: 17,
            cid: 3,
            title: "Content Marketing",
            desc: "Content calendars, blogs, video scripts.",
            type: "not_decided",
            ord: 4,
            dur: 0,
            fid: null,
            status: "pending",
            sched: null,
            link: null,
            notes: null,
            rReason: null,
            rDate: null,
          },
          {
            id: 18,
            cid: 3,
            title: "Analytics & Reporting",
            desc: "GA4, UTM tracking, KPI dashboards.",
            type: "live",
            ord: 5,
            dur: 120,
            fid: 2,
            status: "pending",
            sched: "2025-02-03T14:00",
            link: null,
            notes: null,
            rReason: null,
            rDate: null,
          },
        ],
        workflow: {
          1: [
            {
              k: "email_sent",
              lbl: "Email Sent",
              done: true,
              at: "2024-12-20",
              by: "Sarah",
            },
            {
              k: "meeting_link_shared",
              lbl: "Meeting Link Shared",
              done: true,
              at: "2024-12-21",
              by: "Sarah",
            },
            {
              k: "recording_done",
              lbl: "Recording Done",
              done: true,
              at: "2025-01-10",
              by: "Dr. Priya",
            },
            {
              k: "editing_done",
              lbl: "Editing Done",
              done: true,
              at: "2025-01-12",
              by: "Admin",
            },
            {
              k: "uploaded",
              lbl: "Uploaded",
              done: true,
              at: "2025-01-13",
              by: "Admin",
            },
          ],
          3: [
            {
              k: "banner_created",
              lbl: "Banner Created",
              done: true,
              at: "2025-01-20",
              by: "Sarah",
            },
            {
              k: "website_updated",
              lbl: "Website Updated",
              done: true,
              at: "2025-01-21",
              by: "Admin",
            },
            {
              k: "info_shared_faculty",
              lbl: "Info Shared to Faculty",
              done: true,
              at: "2025-01-23",
              by: "Sarah",
            },
          ],
          4: [
            {
              k: "email_sent",
              lbl: "Email Sent",
              done: true,
              at: "2025-01-22",
              by: "Sarah",
            },
            {
              k: "meeting_link_shared",
              lbl: "Meeting Link Shared",
              done: true,
              at: "2025-01-23",
              by: "Sarah",
            },
            {
              k: "recording_done",
              lbl: "Recording Done",
              done: false,
              at: null,
              by: null,
            },
            {
              k: "editing_done",
              lbl: "Editing Done",
              done: false,
              at: null,
              by: null,
            },
            { k: "uploaded", lbl: "Uploaded", done: false, at: null, by: null },
          ],
        },
        materials: [
          {
            id: 1,
            cid: 1,
            tid: 1,
            mtype: "mcq",
            recv: 40,
            upl: 40,
            notes: null,
          },
          {
            id: 2,
            cid: 1,
            tid: 1,
            mtype: "osce",
            recv: 10,
            upl: 10,
            notes: null,
          },
          {
            id: 3,
            cid: 1,
            tid: 2,
            mtype: "mcq",
            recv: 35,
            upl: 30,
            notes: null,
          },
          {
            id: 4,
            cid: 1,
            tid: 3,
            mtype: "true_false",
            recv: 20,
            upl: 20,
            notes: null,
          },
          {
            id: 5,
            cid: 1,
            tid: null,
            mtype: "spotters",
            recv: 15,
            upl: 10,
            notes: null,
          },
          {
            id: 6,
            cid: 2,
            tid: null,
            mtype: "mcq",
            recv: 50,
            upl: 45,
            notes: null,
          },
          {
            id: 7,
            cid: 2,
            tid: 9,
            mtype: "dicom_long_case",
            recv: 8,
            upl: 6,
            notes: null,
          },
          {
            id: 8,
            cid: 3,
            tid: null,
            mtype: "mcq",
            recv: 60,
            upl: 0,
            notes: "Pending review",
          },
          {
            id: 9,
            cid: 3,
            tid: 14,
            mtype: "osce",
            recv: 12,
            upl: 0,
            notes: null,
          },
          {
            id: 10,
            cid: 2,
            tid: null,
            mtype: "dicom_short_case",
            recv: 10,
            upl: 8,
            notes: null,
          },
        ],
        contracts: [
          {
            id: 1,
            fid: 1,
            cid: 1,
            num: "CNT-2025-001",
            title: "Full Stack Web Dev — Dr. Priya Sharma",
            sd: "2025-01-01",
            ed: "2025-03-31",
            amt: 45000,
            status: "active",
            terms: "8 recorded lectures at Rs 5000 each.",
            signed: "2025-01-02",
          },
          {
            id: 2,
            fid: 2,
            cid: 1,
            num: "CNT-2025-002",
            title: "Full Stack Web Dev — Prof. Rahul Mehta",
            sd: "2025-01-01",
            ed: "2025-03-31",
            amt: 30000,
            status: "active",
            terms: "5 live sessions at Rs 6000 each.",
            signed: "2025-01-03",
          },
          {
            id: 3,
            fid: 3,
            cid: 2,
            num: "CNT-2025-003",
            title: "Data Science — Dr. Anita Desai",
            sd: "2025-01-05",
            ed: "2025-02-28",
            amt: 36000,
            status: "active",
            terms: "6 lectures at Rs 6000 each.",
            signed: "2025-01-06",
          },
          {
            id: 4,
            fid: 2,
            cid: 3,
            num: "CNT-2025-004",
            title: "Digital Marketing — Prof. Rahul Mehta",
            sd: "2025-01-05",
            ed: "2025-02-15",
            amt: 18000,
            status: "completed",
            terms: "3 sessions at Rs 6000 each.",
            signed: "2025-01-05",
          },
          {
            id: 5,
            fid: 1,
            cid: null,
            num: "CNT-2025-005",
            title: "UI/UX Design — Dr. Priya Sharma",
            sd: "2025-02-01",
            ed: "2025-04-30",
            amt: 24000,
            status: "draft",
            terms: "4 recorded lectures.",
            signed: null,
          },
        ],
        payments: [
          {
            id: 1,
            ctid: 1,
            fid: 1,
            inv: "INV-2025-001",
            amt: 15000,
            ptype: "advance",
            status: "paid",
            due: "2025-01-05",
            paid: "2025-01-06",
            method: "Bank Transfer",
            txn: "TXN-001",
          },
          {
            id: 2,
            ctid: 1,
            fid: 1,
            inv: "INV-2025-002",
            amt: 15000,
            ptype: "milestone",
            status: "paid",
            due: "2025-02-01",
            paid: "2025-02-03",
            method: "Bank Transfer",
            txn: "TXN-002",
          },
          {
            id: 3,
            ctid: 1,
            fid: 1,
            inv: "INV-2025-003",
            amt: 15000,
            ptype: "final",
            status: "pending",
            due: "2025-03-31",
            paid: null,
            method: null,
            txn: null,
          },
          {
            id: 4,
            ctid: 2,
            fid: 2,
            inv: "INV-2025-004",
            amt: 15000,
            ptype: "advance",
            status: "paid",
            due: "2025-01-05",
            paid: "2025-01-07",
            method: "UPI",
            txn: "TXN-003",
          },
          {
            id: 5,
            ctid: 2,
            fid: 2,
            inv: "INV-2025-005",
            amt: 15000,
            ptype: "final",
            status: "pending",
            due: "2025-03-31",
            paid: null,
            method: null,
            txn: null,
          },
          {
            id: 6,
            ctid: 3,
            fid: 3,
            inv: "INV-2025-006",
            amt: 18000,
            ptype: "advance",
            status: "paid",
            due: "2025-01-10",
            paid: "2025-01-11",
            method: "Bank Transfer",
            txn: "TXN-004",
          },
          {
            id: 7,
            ctid: 3,
            fid: 3,
            inv: "INV-2025-007",
            amt: 18000,
            ptype: "final",
            status: "processing",
            due: "2025-02-28",
            paid: null,
            method: "Bank Transfer",
            txn: null,
          },
          {
            id: 8,
            ctid: 4,
            fid: 2,
            inv: "INV-2025-008",
            amt: 18000,
            ptype: "final",
            status: "paid",
            due: "2025-02-15",
            paid: "2025-02-16",
            method: "UPI",
            txn: "TXN-005",
          },
        ],
        tasks: [
          {
            id: 1,
            title: "Upload Bootstrap 5 recording",
            desc: "Editing done — upload to platform ASAP.",
            to: 4,
            by: 1,
            module: "topic",
            rid: 4,
            priority: "high",
            due: "2025-02-05",
            status: "open",
            notes: null,
          },
          {
            id: 2,
            title: "Review Digital Marketing MCQs",
            desc: "Client submitted 60 MCQs for approval.",
            to: 5,
            by: 1,
            module: "material",
            rid: 8,
            priority: "medium",
            due: "2025-02-10",
            status: "open",
            notes: null,
          },
          {
            id: 3,
            title: "Finalize Priya Sharma contract",
            desc: "Draft ready, get signature.",
            to: 3,
            by: 1,
            module: "contract",
            rid: 5,
            priority: "urgent",
            due: "2025-02-03",
            status: "in_progress",
            notes: null,
          },
          {
            id: 4,
            title: "Coordinate PHP live session",
            desc: "Share Zoom link and notify faculty.",
            to: 4,
            by: 2,
            module: "topic",
            rid: 5,
            priority: "high",
            due: "2025-02-09",
            status: "open",
            notes: null,
          },
          {
            id: 5,
            title: "Send payment reminder INV-003",
            desc: "Final payment due 31 March.",
            to: 3,
            by: 1,
            module: "payment",
            rid: 3,
            priority: "low",
            due: "2025-03-25",
            status: "open",
            notes: null,
          },
          {
            id: 6,
            title: "Set up DS topics 5-6",
            desc: "Assign faculty and schedule.",
            to: 4,
            by: 1,
            module: "course",
            rid: 2,
            priority: "medium",
            due: "2025-02-15",
            status: "completed",
            notes: null,
          },
        ],
      };

      // ═══════════════════════════════════════════════════════════════
      // CORE UTILS — must be first so all functions below can use them
      // ═══════════════════════════════════════════════════════════════
      const gi = (id) => document.getElementById(id);
      function set(id, h) {
        gi(id).innerHTML = h;
      }
      function esc(s) {
        return (s || "").replace(/'/g, "\\'");
      }
      function fV(id) {
        const e = gi(id);
        return e ? e.value.trim() : "";
      }
      function sV(id) {
        const e = gi(id);
        return e ? e.value : "";
      }
      function iV(id) {
        return parseInt(fV(id)) || 0;
      }
      function fname(id) {
        return D.faculty.find((f) => f.id == id)?.name || "—";
      }
      function cname(id) {
        const c = D.courses.find((c) => c.id == id);
        return c ? c.num + " — " + c.title : "—";
      }
      function tname(id) {
        return D.topics.find((t) => t.id == id)?.title || "—";
      }
      function uname(id) {
        return DEMO_USERS.find((u) => u.id == id)?.name || "—";
      }
      function topics4(cid) {
        return D.topics
          .filter((t) => t.cid == cid)
          .sort((a, b) => a.ord - b.ord);
      }
      function badge(v, cls) {
        return `<span class="bx ${cls || v}">${String(v).replace(/_/g, " ")}</span>`;
      }
      const fmtD = (d) =>
        d
          ? new Date(d).toLocaleDateString("en-IN", {
              day: "numeric",
              month: "short",
              year: "numeric",
            })
          : "—";
      const fmtC = (n) => "₹" + Number(n || 0).toLocaleString("en-IN");
      const today = () => new Date().toISOString().slice(0, 10);
      let _nid = 300;
      const uid = () => ++_nid;

      function toast(msg, type = "suc") {
        const e = document.createElement("div");
        const ic = {
          suc: "bi-check-circle-fill",
          err: "bi-exclamation-circle-fill",
          inf: "bi-info-circle-fill",
        };
        e.className = `toast ${type}`;
        e.innerHTML = `<i class="bi ${ic[type]}"></i><span>${msg}</span>`;
        gi("TW").appendChild(e);
        setTimeout(() => e.remove(), 3000);
      }
      let _dcb = null;
      function OM(cfg) {
        gi("M-icon").innerHTML =
          `<i class="bi ${cfg.icon || "bi-pencil"}"></i>`;
        gi("M-title").textContent = cfg.title || "";
        gi("M-sub").textContent = cfg.sub || "";
        gi("M-body").innerHTML = cfg.body || "";
        gi("M-foot").innerHTML =
          cfg.foot ||
          `<button class="btn bg" onclick="CM()">Cancel</button><button class="btn bp" onclick="${cfg.save}">Save</button>`;
        gi("M-box").className = "mb" + (cfg.lg ? " lg" : "");
        gi("M").classList.add("open");
      }
      function CM() {
        gi("M").classList.remove("open");
      }
      function ODM(msg, cb) {
        gi("DM-msg").textContent = msg;
        _dcb = cb;
        gi("DM").classList.add("open");
      }
      function CDM() {
        gi("DM").classList.remove("open");
        _dcb = null;
      }
      function XDM() {
        if (_dcb) _dcb();
        CDM();
      }
      function swTab(id, btn) {
        document
          .querySelectorAll(".tp")
          .forEach((p) => p.classList.remove("active"));
        document
          .querySelectorAll(".tbtn")
          .forEach((b) => b.classList.remove("active"));
        gi(id)?.classList.add("active");
        btn.classList.add("active");
      }

      // ═══════════════════════════════════════════════════════════════
      // SESSION & AUTH
      // ═══════════════════════════════════════════════════════════════
      let CU = null;
      const ROLES = { owner: 4, admin: 3, accounts: 2, coordinator: 1 };
      const hasRole = (min) => (ROLES[CU?.role] || 0) >= (ROLES[min] || 99);
      const allowedCourses = () =>
        hasRole("admin")
          ? D.courses
          : D.courses.filter((c) => c.coord === CU.id);

      function doLogout() {
        window.location.href = 'logout.php';
      }



      function calcProg(cid) {
        const ts = topics4(cid);
        const lP = ts.length
          ? Math.round(
              (ts.filter((t) => t.status === "completed").length / ts.length) *
                100,
            )
          : 0;
        const mats = D.materials.filter((m) => m.cid == cid);
        const recv = mats.reduce((a, m) => a + m.recv, 0),
          upl = mats.reduce((a, m) => a + m.upl, 0);
        const mP = recv > 0 ? Math.round((upl / recv) * 100) : 0;
        const cnts = D.contracts.filter(
          (c) => c.cid == cid && c.status !== "cancelled",
        );
        const totAmt = cnts.reduce((a, c) => a + c.amt, 0);
        const paidAmt = D.payments
          .filter(
            (p) => cnts.some((c) => c.id === p.ctid) && p.status === "paid",
          )
          .reduce((a, p) => a + p.amt, 0);
        const cP = totAmt > 0 ? Math.round((paidAmt / totAmt) * 100) : 0;
        const overall = Math.round(lP * 0.4 + mP * 0.3 + cP * 0.3);
        return {
          lP,
          mP,
          cP,
          overall,
          lDone: ts.filter((t) => t.status === "completed").length,
          lTotal: ts.length,
          recv,
          upl,
          totAmt,
          paidAmt,
        };
      }

      // ═══════════════════════════════════════════════════════════════
      // NAVIGATION
      // ═══════════════════════════════════════════════════════════════
      let page = "",
        curCid = null;
      function go(p, x) {
        document
          .querySelectorAll(".nl")
          .forEach((l) => l.classList.remove("active"));
        const n = gi("nav-" + p);
        if (n) n.classList.add("active");
        page = p;
        (
          ({
            dashboard: pgDash,
            courses: pgCourses,
            workflow: pgWF,
            materials: pgMat,
            contracts: pgCnt,
            payments: pgPay,
            faculty: pgFac,
            tasks: pgTasks,
            users: pgUsers,
          })[p] || pgDash
        )(x);
      }
      function setTop(t, s, a = "") {
        gi("tb-title").textContent = t;
        gi("tb-sub").textContent = s || "";
        set("tb-actions", a);
      }
      function setC(h) {
        set("content", h);
      }
      function swTab(id, btn) {
        document
          .querySelectorAll(".tp")
          .forEach((p) => p.classList.remove("active"));
        document
          .querySelectorAll(".tbtn")
          .forEach((b) => b.classList.remove("active"));
        gi(id)?.classList.add("active");
        btn.classList.add("active");
      }

      // ═══════════════════════════════════════════════════════════════
      // DASHBOARD
      // ═══════════════════════════════════════════════════════════════
      function pgDash() {
        setTop(
          "Dashboard",
          "Platform overview at a glance",
          `<button class="btn bg bsm" onclick="pgDash()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>`,
        );
        const courses = allowedCourses();
        const tt = D.topics.filter((t) => courses.some((c) => c.id === t.cid));
        const done = tt.filter((t) => t.status === "completed").length;
        const pend = tt.filter((t) => t.status === "pending").length;
        const paid = D.payments
          .filter((p) => p.status === "paid")
          .reduce((a, p) => a + p.amt, 0);
        const pendPay = D.payments
          .filter((p) => p.status === "pending")
          .reduce((a, p) => a + p.amt, 0);
        const myOpenTasks = D.tasks.filter(
          (t) => t.to === CU.id && t.status !== "completed",
        ).length;
        set("badge-c", courses.length);
        set("badge-tasks", myOpenTasks);
        const upcoming = tt
          .filter((t) => t.status === "pending" && t.sched)
          .sort((a, b) => new Date(a.sched) - new Date(b.sched))
          .slice(0, 6);

        const cpBars = courses
          .filter((c) => c.status === "active")
          .map((c) => {
            const p = calcProg(c.id);
            const col =
              p.overall >= 80
                ? "var(--green)"
                : p.overall >= 50
                  ? "var(--accent)"
                  : "var(--yellow)";
            return `<div class="bar-r"><div class="bar-l" title="${c.title}">${c.num}</div><div class="bar-t"><div class="bar-f" style="width:${p.overall}%;background:${col}"></div></div><div class="bar-v" style="color:${col}">${p.overall}%</div></div>`;
          })
          .join("");

        const matBars = courses
          .filter((c) => c.status === "active")
          .map((c) => {
            const m = D.materials.filter((x) => x.cid === c.id);
            const r = m.reduce((a, x) => a + x.recv, 0),
              u = m.reduce((a, x) => a + x.upl, 0);
            const pct = r > 0 ? Math.round((u / r) * 100) : 0;
            return `<div class="bar-r"><div class="bar-l" title="${c.title}">${c.num}</div><div class="bar-t"><div class="bar-f" style="width:${pct}%;background:var(--purple)"></div></div><div class="bar-v">${u}/${r}</div></div>`;
          })
          .join("");

        // Lecture type distribution
        const recCnt = tt.filter((t) => t.type === "recorded").length;
        const livCnt = tt.filter((t) => t.type === "live").length;
        const ndCnt = tt.filter((t) => t.type === "not_decided").length;

        setC(`
  <div class="sg">
    <div class="sc" style="--ca:var(--accent);--ci:var(--ag)"><div class="sc-icon"><i class="bi bi-collection-play-fill"></i></div><div class="sc-val">${courses.length}</div><div class="sc-lbl">Total Courses</div></div>
    <div class="sc" style="--ca:var(--green);--ci:rgba(34,211,160,.12)"><div class="sc-icon"><i class="bi bi-check2-circle"></i></div><div class="sc-val">${done}</div><div class="sc-lbl">Lectures Done</div></div>
    <div class="sc" style="--ca:var(--yellow);--ci:rgba(245,202,66,.12)"><div class="sc-icon"><i class="bi bi-hourglass-split"></i></div><div class="sc-val">${pend}</div><div class="sc-lbl">Pending Lectures</div></div>
    <div class="sc" style="--ca:var(--orange);--ci:rgba(245,146,78,.12)"><div class="sc-icon"><i class="bi bi-check2-square"></i></div><div class="sc-val">${myOpenTasks}</div><div class="sc-lbl">Open Tasks</div></div>
  </div>
  ${
    hasRole("accounts")
      ? `<div class="sg">
    <div class="sc" style="--ca:var(--green);--ci:rgba(34,211,160,.12)"><div class="sc-icon"><i class="bi bi-cash-stack"></i></div><div class="sc-val" style="font-size:17px">${fmtC(paid)}</div><div class="sc-lbl">Total Paid Out</div></div>
    <div class="sc" style="--ca:var(--yellow);--ci:rgba(245,202,66,.12)"><div class="sc-icon"><i class="bi bi-clock-history"></i></div><div class="sc-val" style="font-size:17px">${fmtC(pendPay)}</div><div class="sc-lbl">Payments Pending</div></div>
    <div class="sc" style="--ca:var(--accent);--ci:var(--ag)"><div class="sc-icon"><i class="bi bi-file-earmark-check-fill"></i></div><div class="sc-val">${D.contracts.filter((c) => c.status === "active").length}</div><div class="sc-lbl">Active Contracts</div></div>
    <div class="sc" style="--ca:var(--purple);--ci:rgba(167,139,250,.12)"><div class="sc-icon"><i class="bi bi-folder2-open"></i></div><div class="sc-val">${D.materials.length}</div><div class="sc-lbl">Material Entries</div></div>
  </div>`
      : ``
  }
  <div class="dr">
    <div class="dc"><div class="dct"><i class="bi bi-bar-chart-line"></i>Course Progress (Lecture×40 + Material×30 + Contract×30)</div>
      ${cpBars || '<div class="tx-m" style="font-size:13px">No active courses</div>'}
    </div>
    <div class="dc"><div class="dct"><i class="bi bi-cloud-upload"></i>Material Tracking (Uploaded / Received)</div>
      ${matBars || '<div class="tx-m" style="font-size:13px">No materials logged</div>'}
    </div>
  </div>
  <div class="dr">
    <div class="dc"><div class="dct"><i class="bi bi-pie-chart"></i>Lecture Type Distribution</div>
      <div class="bar-r"><div class="bar-l">Recorded</div><div class="bar-t"><div class="bar-f" style="width:${tt.length ? Math.round((recCnt / tt.length) * 100) : 0}%;background:var(--accent2)"></div></div><div class="bar-v">${recCnt}</div></div>
      <div class="bar-r"><div class="bar-l">Live</div><div class="bar-t"><div class="bar-f" style="width:${tt.length ? Math.round((livCnt / tt.length) * 100) : 0}%;background:var(--red)"></div></div><div class="bar-v">${livCnt}</div></div>
      <div class="bar-r"><div class="bar-l">Not Decided</div><div class="bar-t"><div class="bar-f" style="width:${tt.length ? Math.round((ndCnt / tt.length) * 100) : 0}%;background:var(--text3)"></div></div><div class="bar-v">${ndCnt}</div></div>
      <div class="bar-r"><div class="bar-l">Completed</div><div class="bar-t"><div class="bar-f" style="width:${tt.length ? Math.round((done / tt.length) * 100) : 0}%;background:var(--green)"></div></div><div class="bar-v">${done}</div></div>
    </div>
    <div class="dc"><div class="dct"><i class="bi bi-person-badge"></i>Faculty Workload</div>
      ${D.faculty
        .slice(0, 5)
        .map((f) => {
          const tc = D.topics.filter((t) => t.fid === f.id).length;
          const dc = D.topics.filter(
            (t) => t.fid === f.id && t.status === "completed",
          ).length;
          const pct = tc > 0 ? Math.round((dc / tc) * 100) : 0;
          return `<div class="bar-r"><div class="bar-l" title="${f.name}">${f.name.split(" ").slice(-1)[0]}</div><div class="bar-t"><div class="bar-f" style="width:${pct}%;background:var(--green)"></div></div><div class="bar-v">${dc}/${tc}</div></div>`;
        })
        .join("")}
    </div>
  </div>
  <div class="dc" style="margin-bottom:13px"><div class="dct"><i class="bi bi-calendar-event"></i>Upcoming Lectures</div>
    ${
      upcoming.length
        ? upcoming
            .map((t) => {
              const c = D.courses.find((x) => x.id === t.cid);
              const col =
                t.type === "live"
                  ? "var(--red)"
                  : t.type === "recorded"
                    ? "var(--accent)"
                    : "var(--text3)";
              return `<div class="upi"><div style="width:7px;height:7px;border-radius:50%;background:${col};flex-shrink:0"></div><div style="flex:1;min-width:0"><div style="font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${t.title}</div><div style="font-size:11.5px;color:var(--text3)">${c?.num || "—"} · ${fmtD(t.sched)}</div></div>${badge(t.type, t.type)}</div>`;
            })
            .join("")
        : '<div class="tx-m" style="padding:14px 0;font-size:13px">No upcoming lectures</div>'
    }
  </div>`);
      }

      // ═══════════════════════════════════════════════════════════════
      // COURSES
      // ═══════════════════════════════════════════════════════════════
      let cView = "cards",
        cSearch = "",
        cSt = "",
        cLv = "";
      function pgCourses() {
        const courses = allowedCourses();
        set("badge-c", courses.length);
        setTop(
          "Courses",
          "Course catalogue with live progress",
          `<button class="btn bg bsm" onclick="pgCourses()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
     ${hasRole("admin") ? `<button class="btn bp" onclick="openCF()"><i class="bi bi-plus-lg"></i> New Course</button>` : ""}`,
        );
        let list = [...courses];
        if (cSearch)
          list = list.filter(
            (c) =>
              c.title.toLowerCase().includes(cSearch.toLowerCase()) ||
              c.num.toLowerCase().includes(cSearch.toLowerCase()) ||
              (c.cat || "").toLowerCase().includes(cSearch.toLowerCase()),
          );
        if (cSt) list = list.filter((c) => c.status === cSt);
        if (cLv) list = list.filter((c) => c.level === cLv);
        const stats = {
          total: courses.length,
          active: courses.filter((c) => c.status === "active").length,
          draft: courses.filter((c) => c.status === "draft").length,
          done: courses.filter((c) => c.status === "completed").length,
        };
        setC(`
  <div class="sg">
    <div class="sc" style="--ca:var(--accent);--ci:var(--ag)"><div class="sc-icon"><i class="bi bi-collection-play"></i></div><div class="sc-val">${stats.total}</div><div class="sc-lbl">Total Courses</div></div>
    <div class="sc" style="--ca:var(--green);--ci:rgba(34,211,160,.12)"><div class="sc-icon"><i class="bi bi-check2-circle"></i></div><div class="sc-val">${stats.active}</div><div class="sc-lbl">Active</div></div>
    <div class="sc" style="--ca:var(--yellow);--ci:rgba(245,202,66,.12)"><div class="sc-icon"><i class="bi bi-pencil-square"></i></div><div class="sc-val">${stats.draft}</div><div class="sc-lbl">Draft</div></div>
    <div class="sc" style="--ca:var(--purple);--ci:rgba(167,139,250,.12)"><div class="sc-icon"><i class="bi bi-trophy"></i></div><div class="sc-val">${stats.done}</div><div class="sc-lbl">Completed</div></div>
  </div>
  <div class="tb">
    <div class="sbw"><i class="bi bi-search"></i><input type="text" placeholder="Search courses…" value="${cSearch}" oninput="cSearch=this.value;pgCourses()"></div>
    <select class="fsel" onchange="cSt=this.value;pgCourses()"><option value="">All Status</option>${["active", "draft", "completed", "archived"].map((s) => `<option value="${s}" ${cSt === s ? "selected" : ""}>${s}</option>`).join("")}</select>
    <select class="fsel" onchange="cLv=this.value;pgCourses()"><option value="">All Levels</option>${["beginner", "intermediate", "advanced"].map((s) => `<option value="${s}" ${cLv === s ? "selected" : ""}>${s}</option>`).join("")}</select>
    <div class="vt"><button class="vb ${cView === "cards" ? "active" : ""}" onclick="cView='cards';pgCourses()"><i class="bi bi-grid-3x3-gap"></i></button><button class="vb ${cView === "list" ? "active" : ""}" onclick="cView='list';pgCourses()"><i class="bi bi-list-ul"></i></button></div>
  </div>
  ${
    !list.length
      ? `<div class="es"><div class="es-i"><i class="bi bi-collection-play"></i></div><div class="es-t">No courses found</div><div class="es-s">Adjust filters or create a new course.</div>${hasRole("admin") ? `<button class="btn bp" onclick="openCF()"><i class="bi bi-plus-lg"></i> New Course</button>` : ""}</div>`
      : cView === "cards"
        ? `<div class="cg">${list.map(cCard).join("")}</div>`
        : `<div class="tw"><table class="ct"><thead><tr><th>Number</th><th>Title</th><th>Category</th><th>Level</th><th>Status</th><th>Progress</th><th>Topics</th><th></th></tr></thead><tbody>${list.map(cRow).join("")}</tbody></table></div>`
  }`);
      }

      function cCard(c) {
        const p = calcProg(c.id),
          ct = topics4(c.id);
        const coord = DEMO_USERS.find((u) => u.id === c.coord);
        const pCol =
          p.overall >= 80
            ? "var(--green)"
            : p.overall >= 50
              ? "var(--accent)"
              : "var(--yellow)";
        return `<div class="cc" onclick="viewCourse(${c.id})">
    <div class="cc-stripe"></div>
    <div class="mono" style="font-size:11px;color:var(--accent);margin-bottom:4px">${c.num}</div>
    <div style="font-size:14px;font-weight:600;margin-bottom:5px;line-height:1.35">${c.title}</div>
    <div style="font-size:12.5px;color:var(--text2);line-height:1.5;margin-bottom:10px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">${c.desc || ""}</div>
    <div style="display:flex;gap:9px;flex-wrap:wrap;margin-bottom:10px">
      <span style="font-size:12px;color:var(--text3)"><i class="bi bi-tag"></i> ${c.cat || "—"}</span>
      <span style="font-size:12px;color:var(--text3)"><i class="bi bi-camera-video"></i> ${ct.length} Topics</span>
      ${coord ? `<span style="font-size:12px;color:var(--text3)"><i class="bi bi-person"></i> ${coord.name.split(" ")[0]}</span>` : ""}
    </div>
    <div class="pb-lbl"><span>Overall Progress</span><span style="font-weight:700;color:${pCol}">${p.overall}%</span></div>
    <div class="pb-bg"><div class="pb-fill" style="width:${p.overall}%"></div></div>
    <div class="prog3">
      <div class="p3seg"><div class="p3v" style="color:var(--accent)">${p.lP}%</div><div class="p3l">Lecture ×40%</div></div>
      <div class="p3seg"><div class="p3v" style="color:var(--purple)">${p.mP}%</div><div class="p3l">Material ×30%</div></div>
      <div class="p3seg"><div class="p3v" style="color:var(--green)">${p.cP}%</div><div class="p3l">Contract ×30%</div></div>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;border-top:1px solid var(--border);padding-top:10px">
      <div style="display:flex;gap:5px">${badge(c.status)} ${badge(c.level)}</div>
      ${
        hasRole("admin")
          ? `<div style="display:flex;gap:4px" onclick="event.stopPropagation()">
        <button class="btn bg bsm bic" onclick="openCF(${c.id})"><i class="bi bi-pencil"></i></button>
        <button class="btn bg bsm bic" onclick="delCourse(${c.id},'${esc(c.title)}')"><i class="bi bi-trash3" style="color:var(--red)"></i></button>
      </div>`
          : ""
      }
    </div>
  </div>`;
      }

      function cRow(c) {
        const p = calcProg(c.id),
          ct = topics4(c.id).length;
        const pCol =
          p.overall >= 80
            ? "var(--green)"
            : p.overall >= 50
              ? "var(--accent)"
              : "var(--yellow)";
        return `<tr style="cursor:pointer" onclick="viewCourse(${c.id})">
    <td><span class="mono tx-a" style="font-size:11.5px">${c.num}</span></td><td><strong>${c.title}</strong></td>
    <td style="color:var(--text2)">${c.cat || "—"}</td><td>${badge(c.level)}</td><td>${badge(c.status)}</td>
    <td style="min-width:130px"><div class="pb-lbl" style="margin-bottom:1px"><span style="font-weight:700;color:${pCol}">${p.overall}%</span></div><div class="pb-bg" style="height:4px"><div class="pb-fill" style="width:${p.overall}%"></div></div></td>
    <td class="tx-a">${ct}</td>
    <td onclick="event.stopPropagation()">${hasRole("admin") ? `<div style="display:flex;gap:4px;justify-content:flex-end"><button class="btn bg bsm bic" onclick="openCF(${c.id})"><i class="bi bi-pencil"></i></button><button class="btn bg bsm bic" onclick="delCourse(${c.id},'${esc(c.title)}')"><i class="bi bi-trash3" style="color:var(--red)"></i></button></div>` : ""}</td>
  </tr>`;
      }

      function openCF(id) {
        const c = id ? D.courses.find((x) => x.id == id) : null;
        const co = DEMO_USERS.filter((u) => u.role === "coordinator")
          .map(
            (u) =>
              `<option value="${u.id}" ${c?.coord == u.id ? "selected" : ""}>${u.name}</option>`,
          )
          .join("");
        OM({
          icon: "bi-collection-play",
          title: c ? "Edit Course" : "New Course",
          sub: c ? c.num : "Auto number will be generated (CRS-YYYY-XXX)",
          save: `saveCF(${id || "null"})`,
          body: `<div class="fr">
      <div class="fg"><label class="fl">Title <span>*</span></label><input class="fi" id="cf-t" value="${esc(c?.title || "")}"></div>
      <div class="fg"><label class="fl">Course Number</label><input class="fi" value="${c?.num || "Auto: CRS-" + new Date().getFullYear() + "-XXX"}" disabled style="opacity:.55;font-family:var(--mono)"></div>
    </div>
    <div class="fg"><label class="fl">Description</label><textarea class="fta" id="cf-d" style="min-height:60px">${c?.desc || ""}</textarea></div>
    <div class="fr">
      <div class="fg"><label class="fl">Category</label><input class="fi" id="cf-cat" value="${esc(c?.cat || "")}"></div>
      <div class="fg"><label class="fl">Level</label><select class="fsi" id="cf-lv">${["beginner", "intermediate", "advanced"].map((l) => `<option value="${l}" ${c?.level === l ? "selected" : ""}>${l.charAt(0).toUpperCase() + l.slice(1)}</option>`).join("")}</select></div>
    </div>
    <div class="fr">
      <div class="fg"><label class="fl">Status</label><select class="fsi" id="cf-st">${["draft", "active", "completed", "archived"].map((s) => `<option value="${s}" ${(c?.status || "draft") === s ? "selected" : ""}>${s.charAt(0).toUpperCase() + s.slice(1)}</option>`).join("")}</select></div>
      <div class="fg"><label class="fl">Coordinator</label><select class="fsi" id="cf-co"><option value="">— None —</option>${co}</select></div>
    </div>`,
        });
        if (c) {
          setTimeout(() => {
            if (gi("cf-co")) gi("cf-co").value = c.coord || "";
          }, 50);
        }
      }

      function saveCF(id) {
        const t = fV("cf-t");
        if (!t) {
          toast("Title required", "err");
          return;
        }
        if (id) {
          const c = D.courses.find((x) => x.id == id);
          Object.assign(c, {
            title: t,
            desc: fV("cf-d"),
            cat: fV("cf-cat"),
            level: sV("cf-lv"),
            status: sV("cf-st"),
            coord: parseInt(sV("cf-co")) || null,
          });
          toast("Course updated");
        } else {
          const yr = new Date().getFullYear(),
            cnt =
              D.courses.filter((c) => c.num.startsWith("CRS-" + yr)).length + 1;
          const num = "CRS-" + yr + "-" + String(cnt).padStart(3, "0");
          D.courses.unshift({
            id: uid(),
            num,
            title: t,
            desc: fV("cf-d"),
            cat: fV("cf-cat"),
            level: sV("cf-lv"),
            status: sV("cf-st"),
            coord: parseInt(sV("cf-co")) || null,
          });
          toast("Course created — " + num);
        }
        CM();
        pgCourses();
      }
      function delCourse(id, name) {
        ODM(`Delete "${name}"? All topics removed.`, () => {
          D.courses = D.courses.filter((c) => c.id != id);
          D.topics = D.topics.filter((t) => t.cid != id);
          toast("Course deleted");
          pgCourses();
        });
      }

      // ── COURSE DETAIL ──
      function viewCourse(cid) {
        curCid = cid;
        const c = D.courses.find((x) => x.id == cid);
        if (!c) return;
        if (!hasRole("admin") && c.coord !== CU.id) {
          toast("Access restricted to your assigned course", "err");
          return;
        }
        const ts = topics4(cid),
          p = calcProg(cid);
        const totM = ts.reduce((a, t) => a + (t.dur || 0), 0);
        const pCol =
          p.overall >= 80
            ? "var(--green)"
            : p.overall >= 50
              ? "var(--accent)"
              : "var(--yellow)";
        setTop(
          c.title,
          `${c.num} · ${c.cat || "Uncategorized"}`,
          `<button class="btn bg bsm" onclick="pgCourses()"><i class="bi bi-arrow-left"></i> Back</button>
     ${hasRole("admin") ? `<button class="btn bg bsm" onclick="openCF(${cid})"><i class="bi bi-pencil"></i> Edit</button>` : ""}
     <button class="btn bp" onclick="openTF(null,${cid})"><i class="bi bi-plus-lg"></i> Add Topic</button>`,
        );
        setC(`
  <div class="bc"><a onclick="pgCourses()">Courses</a><i class="bi bi-chevron-right"></i><span>${c.title}</span></div>
  <div class="dh">
    <div class="dhicon"><i class="bi bi-collection-play-fill"></i></div>
    <div style="flex:1;min-width:0">
      <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px"><span class="mono tx-a" style="font-size:12px">${c.num}</span>${badge(c.status)} ${badge(c.level)}</div>
      <div class="dhtitle">${c.title}</div>
      <div style="color:var(--text2);font-size:13px;margin-top:2px">${c.desc || ""}</div>
      <div class="dhmeta">
        <span><i class="bi bi-tag"></i>${c.cat || "—"}</span>
        <span><i class="bi bi-camera-video"></i>${ts.length} Topics</span>
        <span><i class="bi bi-clock"></i>${Math.floor(totM / 60)}h ${totM % 60}m</span>
        <span><i class="bi bi-person"></i>${DEMO_USERS.find((u) => u.id === c.coord)?.name || "—"}</span>
      </div>
      <div style="margin-top:10px">
        <div class="pb-lbl"><span style="font-size:12px">Overall = Lecture(${p.lP}%)×40 + Material(${p.mP}%)×30 + Contract(${p.cP}%)×30</span><span style="font-size:15px;font-weight:800;color:${pCol}">${p.overall}%</span></div>
        <div class="pb-bg" style="height:7px"><div class="pb-fill" style="width:${p.overall}%"></div></div>
        <div class="prog3" style="margin-top:9px">
          <div class="p3seg"><div class="p3v" style="color:var(--accent)">${p.lP}%</div><div class="p3l">Lecture ×40%<br><span style="color:var(--text3)">${p.lDone}/${p.lTotal} done</span></div></div>
          <div class="p3seg"><div class="p3v" style="color:var(--purple)">${p.mP}%</div><div class="p3l">Material ×30%<br><span style="color:var(--text3)">${p.upl}/${p.recv} uploaded</span></div></div>
          <div class="p3seg"><div class="p3v" style="color:var(--green)">${p.cP}%</div><div class="p3l">Contract ×30%<br><span style="color:var(--text3)">${fmtC(p.paidAmt)} paid</span></div></div>
        </div>
      </div>
    </div>
  </div>
  <div class="tbar">
    <button class="tbtn active" onclick="swTab('ta',this)">All <span style="color:var(--text3);margin-left:3px">${ts.length}</span></button>
    <button class="tbtn" onclick="swTab('trec',this)">Recorded <span style="color:var(--text3);margin-left:3px">${ts.filter((t) => t.type === "recorded").length}</span></button>
    <button class="tbtn" onclick="swTab('tlive',this)">Live <span style="color:var(--text3);margin-left:3px">${ts.filter((t) => t.type === "live").length}</span></button>
    <button class="tbtn" onclick="swTab('tnd',this)">Not Decided <span style="color:var(--text3);margin-left:3px">${ts.filter((t) => t.type === "not_decided").length}</span></button>
    <button class="tbtn" onclick="swTab('tpen',this)">Pending <span style="color:var(--text3);margin-left:3px">${ts.filter((t) => t.status === "pending").length}</span></button>
    <button class="tbtn" onclick="swTab('tdone',this)">Done <span style="color:var(--text3);margin-left:3px">${ts.filter((t) => t.status === "completed").length}</span></button>
  </div>
  <div id="ta" class="tp active">${topicList(ts)}</div>
  <div id="trec" class="tp">${topicList(ts.filter((t) => t.type === "recorded"))}</div>
  <div id="tlive" class="tp">${topicList(ts.filter((t) => t.type === "live"))}</div>
  <div id="tnd" class="tp">${topicList(ts.filter((t) => t.type === "not_decided"))}</div>
  <div id="tpen" class="tp">${topicList(ts.filter((t) => t.status === "pending"))}</div>
  <div id="tdone" class="tp">${topicList(ts.filter((t) => t.status === "completed"))}</div>`);
      }

      function topicList(ts) {
        if (!ts.length)
          return `<div class="es"><div class="es-i"><i class="bi bi-camera-video-off"></i></div><div class="es-t">No topics here</div><div class="es-s">Add topics using the button above.</div></div>`;
        return ts
          .map((t) => {
            const sc =
              t.status === "completed"
                ? "completed"
                : t.status === "in_progress"
                  ? "in_progress"
                  : t.status === "rescheduled"
                    ? "rescheduled"
                    : t.status === "cancelled"
                      ? "archived"
                      : "pending";
            const wf = D.workflow[t.id],
              wd = wf ? wf.filter((s) => s.done).length : 0;
            return `<div class="ti">
      <div class="tnum">${t.ord}</div>
      <div class="tinfo">
        <div class="ttitle">${t.title}</div>
        <div class="tdesc">${t.desc || "No description"}${t.rReason ? ` | 🔄 Rescheduled: ${t.rReason}` : ""}</div>
      </div>
      <div class="tmeta">
        ${t.dur ? `<span style="font-size:11.5px;color:var(--text3)"><i class="bi bi-clock"></i> ${t.dur}m</span>` : ""}
        ${t.sched ? `<span style="font-size:11.5px;color:var(--text3)"><i class="bi bi-calendar3"></i> ${fmtD(t.sched)}</span>` : ""}
        ${t.fid ? `<span style="font-size:11.5px;color:var(--text2)"><i class="bi bi-person"></i> ${fname(t.fid)}</span>` : ""}
        ${badge(t.type, t.type)}<span class="bx ${sc}">${t.status.replace("_", " ")}</span>
        ${wf && t.type !== "not_decided" ? `<span style="font-size:11px;color:var(--text3)">${wd}/${wf.length} ✓</span>` : ""}
        ${t.link ? `<a href="${t.link}" target="_blank" class="btn bg bsm" style="padding:2px 8px;font-size:11px" onclick="event.stopPropagation()"><i class="bi bi-camera-video-fill"></i> Join</a>` : ""}
      </div>
      <div class="tact">
        ${t.type !== "not_decided" ? `<button class="btn bg bsm bic" onclick="openWF2(${t.id})" title="Workflow"><i class="bi bi-diagram-3"></i></button>` : ""}
        ${["pending", "in_progress"].includes(t.status) ? `<button class="btn bg bsm bic" onclick="openReschedule(${t.id})" title="Reschedule"><i class="bi bi-calendar-x"></i></button>` : ""}
        <button class="btn bg bsm bic" onclick="openTF(${t.id},${t.cid})"><i class="bi bi-pencil"></i></button>
        <button class="btn bg bsm bic" onclick="delT(${t.id},'${esc(t.title)}')"><i class="bi bi-trash3" style="color:var(--red)"></i></button>
      </div>
    </div>`;
          })
          .join("");
      }

      function openTF(id, cid) {
        const t = id ? D.topics.find((x) => x.id == id) : null;
        const fo = D.faculty
          .filter((f) => f.status === "active")
          .map(
            (f) =>
              `<option value="${f.id}" ${t?.fid == f.id ? "selected" : ""}>${f.name} — ${f.designation}</option>`,
          )
          .join("");
        OM({
          icon: "bi-camera-video",
          title: t ? "Edit Topic" : "New Topic",
          sub: "Lecture details",
          save: `saveTF(${id || "null"},${cid})`,
          body: `<div class="fg"><label class="fl">Title <span>*</span></label><input class="fi" id="tf-t" value="${esc(t?.title || "")}"></div>
    <div class="fg"><label class="fl">Description</label><textarea class="fta" id="tf-d" style="min-height:55px">${t?.desc || ""}</textarea></div>
    <div class="fr">
      <div class="fg"><label class="fl">Lecture Type</label><select class="fsi" id="tf-type" onchange="toggleTFD()">${["not_decided", "recorded", "live"].map((v) => `<option value="${v}" ${(t?.type || "not_decided") === v ? "selected" : ""}>${v.replace("_", " ")}</option>`).join("")}</select></div>
      <div class="fg"><label class="fl">Status</label><select class="fsi" id="tf-st">${["pending", "in_progress", "completed", "cancelled", "rescheduled"].map((v) => `<option value="${v}" ${(t?.status || "pending") === v ? "selected" : ""}>${v.replace("_", " ")}</option>`).join("")}</select></div>
    </div>
    <div id="tf-extra">
      <div class="fr">
        <div class="fg"><label class="fl">Duration (min)</label><input type="number" class="fi" id="tf-dur" value="${t?.dur || ""}"></div>
        <div class="fg"><label class="fl">Faculty</label><select class="fsi" id="tf-fac"><option value="">— Select Faculty —</option>${fo}</select></div>
      </div>
      <div class="fr">
        <div class="fg"><label class="fl">Scheduled At</label><input type="datetime-local" class="fi" id="tf-sc" value="${t?.sched ? t.sched.slice(0, 16) : ""}"></div>
        <div class="fg"><label class="fl">Meeting Link</label><input class="fi" id="tf-lnk" value="${esc(t?.link || "")}"></div>
      </div>
    </div>
    <div class="fg"><label class="fl">Notes</label><textarea class="fta" id="tf-n" style="min-height:50px">${t?.notes || ""}</textarea></div>`,
        });
        if (t?.fid)
          setTimeout(() => {
            if (gi("tf-fac")) gi("tf-fac").value = t.fid;
          }, 50);
        toggleTFD();
      }
      function toggleTFD() {
        const d = gi("tf-extra");
        if (d) d.style.display = sV("tf-type") === "not_decided" ? "none" : "";
      }

      function saveTF(id, cid) {
        const t = fV("tf-t");
        if (!t) {
          toast("Title required", "err");
          return;
        }
        if (id) {
          const x = D.topics.find((x) => x.id == id);
          Object.assign(x, {
            title: t,
            desc: fV("tf-d"),
            type: sV("tf-type"),
            status: sV("tf-st"),
            dur: parseInt(fV("tf-dur")) || 0,
            fid: parseInt(sV("tf-fac")) || null,
            sched: fV("tf-sc") || null,
            link: fV("tf-lnk") || null,
            notes: fV("tf-n") || null,
          });
          toast("Topic updated");
        } else {
          const mo = D.topics
            .filter((x) => x.cid == cid)
            .reduce((m, x) => Math.max(m, x.ord), 0);
          D.topics.push({
            id: uid(),
            cid: parseInt(cid),
            title: t,
            desc: fV("tf-d"),
            type: sV("tf-type"),
            ord: mo + 1,
            dur: parseInt(fV("tf-dur")) || 0,
            fid: parseInt(sV("tf-fac")) || null,
            status: sV("tf-st"),
            sched: fV("tf-sc") || null,
            link: fV("tf-lnk") || null,
            notes: fV("tf-n") || null,
            rReason: null,
            rDate: null,
          });
          toast("Topic created");
        }
        CM();
        viewCourse(cid);
      }
      function delT(id, name) {
        ODM(`Delete topic "${name}"?`, () => {
          D.topics = D.topics.filter((t) => t.id != id);
          delete D.workflow[id];
          toast("Deleted");
          viewCourse(curCid);
        });
      }

      function openReschedule(tid) {
        const t = D.topics.find((x) => x.id == tid);
        if (!t) return;
        OM({
          icon: "bi-calendar-x",
          title: "Reschedule Topic",
          sub: t.title,
          save: `saveReschedule(${tid})`,
          body: `<div class="fg"><label class="fl">Reason for Rescheduling <span>*</span></label>
      <textarea class="fta" id="rs-r" placeholder="Faculty unavailable / Technical issue / Client request…">${t.rReason || ""}</textarea></div>
    <div class="fg"><label class="fl">New Scheduled Date & Time <span>*</span></label><input type="datetime-local" class="fi" id="rs-d" value="${t.rDate ? t.rDate.slice(0, 16) : ""}"></div>
    <div class="rs-box"><div style="font-size:12px;color:var(--yellow);font-weight:600;margin-bottom:4px"><i class="bi bi-exclamation-triangle"></i> Previous schedule: ${fmtD(t.sched)}</div><div style="font-size:12px;color:var(--text3)">Topic status will be marked as "Rescheduled".</div></div>`,
        });
      }
      function saveReschedule(tid) {
        const reason = fV("rs-r"),
          date = fV("rs-d");
        if (!reason || !date) {
          toast("Reason and date required", "err");
          return;
        }
        const t = D.topics.find((x) => x.id == tid);
        if (!t) return;
        t.rReason = reason;
        t.rDate = date;
        t.sched = date;
        t.status = "rescheduled";
        toast("Topic rescheduled", "inf");
        CM();
        viewCourse(curCid);
      }

      // ═══════════════════════════════════════════════════════════════
      // WORKFLOW
      // ═══════════════════════════════════════════════════════════════
      const WF_REC = [
        { k: "email_sent", lbl: "Email Sent" },
        { k: "meeting_link_shared", lbl: "Meeting Link Shared" },
        { k: "recording_done", lbl: "Recording Done" },
        { k: "editing_done", lbl: "Editing Done" },
        { k: "uploaded", lbl: "Uploaded" },
      ];
      const WF_LIVE = [
        { k: "banner_created", lbl: "Banner Created" },
        { k: "website_updated", lbl: "Website Updated" },
        { k: "info_shared_faculty", lbl: "Info Shared to Faculty" },
      ];

      let wfCF = "";
      function pgWF() {
        setTop(
          "Workflow",
          "Lecture production checklist",
          '<button class="btn bg bsm" onclick="pgWF()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>',
        );
        const courses = allowedCourses();
        setC(`<div class="tb"><select class="fsel" style="max-width:310px" onchange="wfCF=parseInt(this.value)||'';pgWF()"><option value="">All Courses</option>${courses.map((c) => `<option value="${c.id}" ${wfCF == c.id ? "selected" : ""}>${c.num} — ${c.title}</option>`).join("")}</select></div>
  ${courses
    .filter((c) => !wfCF || c.id == wfCF)
    .map((c) => {
      const ts = topics4(c.id).filter((t) => t.type !== "not_decided");
      return `<div style="margin-bottom:20px"><div class="sec-t" style="display:flex;align-items:center;gap:7px"><span class="mono tx-a">${c.num}</span>${c.title}</div>
    ${
      !ts.length
        ? '<div class="tx-m" style="font-size:13px;padding:7px 0">No recorded/live topics yet.</div>'
        : `<div class="tw"><table class="ct"><thead><tr><th>#</th><th>Lecture</th><th>Type</th><th>Faculty</th><th>Workflow Progress</th><th>Action</th></tr></thead><tbody>
    ${ts
      .map((t) => {
        const wf = D.workflow[t.id],
          wd = wf ? wf.filter((s) => s.done).length : 0,
          wt = wf ? wf.length : 0,
          pct = wt ? Math.round((wd / wt) * 100) : 0;
        return `<tr><td class="mono tx-a">${t.ord}</td>
      <td><div style="font-weight:500">${t.title}</div><div style="font-size:11.5px;color:var(--text3)">${fmtD(t.sched)}${t.rReason ? ` | 🔄 ${t.rReason}` : ""}</div></td>
      <td>${badge(t.type, t.type)}</td><td style="font-size:13px;color:var(--text2)">${fname(t.fid)}</td>
      <td style="min-width:150px">${wf ? `<div class="pb-lbl"><span>${wd}/${wt} steps</span><span>${pct}%</span></div><div class="pb-bg"><div class="pb-fill" style="width:${pct}%"></div></div>` : `<button class="btn bg bsm" onclick="initWF(${t.id},'${t.type}')"><i class="bi bi-plus"></i> Initialize</button>`}</td>
      <td><button class="btn bg bsm" onclick="openWF2(${t.id})"><i class="bi bi-diagram-3"></i> Checklist</button></td>
    </tr>`;
      })
      .join("")}</tbody></table></div>`
    }
    </div>`;
    })
    .join("")}`);
      }

      function initWF(tid, type) {
        D.workflow[tid] = (type === "live" ? WF_LIVE : WF_REC).map((s) => ({
          k: s.k,
          lbl: s.lbl,
          done: false,
          at: null,
          by: null,
        }));
        toast("Workflow initialized");
        pgWF();
      }

      function openWF2(tid) {
        const t = D.topics.find((x) => x.id == tid);
        if (!t) return;
        if (!D.workflow[tid])
          D.workflow[tid] = (t.type === "live" ? WF_LIVE : WF_REC).map((s) => ({
            k: s.k,
            lbl: s.lbl,
            done: false,
            at: null,
            by: null,
          }));
        const wf = D.workflow[tid],
          dn = wf.filter((s) => s.done).length,
          pct = Math.round((dn / wf.length) * 100);
        const liveFooter =
          t.type === "live"
            ? `<div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border)"><div class="sec-t" style="margin-bottom:8px">Final Status</div><div style="display:flex;gap:7px;flex-wrap:wrap"><button class="btn bs bsm" onclick="setTopicSt(${tid},'completed')"><i class="bi bi-check2-all"></i> Completed</button><button class="btn bd bsm" onclick="setTopicSt(${tid},'cancelled')"><i class="bi bi-x-circle"></i> Cancelled</button><button class="btn bw bsm" onclick="CM();setTimeout(()=>openReschedule(${tid}),200)"><i class="bi bi-calendar-x"></i> Rescheduled</button></div></div>`
            : "";
        OM({
          icon: "bi-diagram-3",
          title: t.title,
          sub: `${t.type === "live" ? "Live Event" : "Recording"} checklist · ${dn}/${wf.length} complete`,
          lg: true,
          foot: `<button class="btn bg" onclick="CM()">Close</button>`,
          body: `<div style="margin-bottom:12px"><div class="pb-lbl"><span>Progress</span><span>${pct}%</span></div><div class="pb-bg"><div class="pb-fill" style="width:${pct}%"></div></div></div>
    <div class="wft">${wf
      .map(
        (s) => `<div class="wfs" id="ws-${tid}-${s.k}">
      <div class="wfd${s.done ? " done" : ""}" onclick="togWF(${tid},'${s.k}')"><i class="bi ${s.done ? "bi-check" : "bi-circle"}"></i></div>
      <div class="wfi"><div class="wfl" style="${s.done ? "text-decoration:line-through;color:var(--text3)" : ""}">${s.lbl}</div>
      <div class="wfm">${s.done ? `Completed ${s.at || ""} by ${s.by || ""}` : "Not completed yet"}</div></div>
    </div>`,
      )
      .join("")}</div>${liveFooter}`,
        });
      }

      function togWF(tid, k) {
        const wf = D.workflow[tid],
          s = wf.find((x) => x.k === k);
        if (!s) return;
        s.done = !s.done;
        s.at = s.done ? today() : null;
        s.by = s.done ? CU.name : null;
        const dot = gi(`ws-${tid}-${k}`)?.querySelector(".wfd");
        const lbl = gi(`ws-${tid}-${k}`)?.querySelector(".wfl");
        const meta = gi(`ws-${tid}-${k}`)?.querySelector(".wfm");
        if (dot) {
          dot.className = `wfd${s.done ? " done" : ""}`;
          dot.innerHTML = `<i class="bi ${s.done ? "bi-check" : "bi-circle"}"></i>`;
        }
        if (lbl)
          lbl.style.cssText = s.done
            ? "text-decoration:line-through;color:var(--text3)"
            : "";
        if (meta)
          meta.textContent = s.done
            ? `Completed ${s.at} by ${s.by}`
            : "Not completed yet";
        const dn = wf.filter((x) => x.done).length;
        const sub = gi("M-sub");
        if (sub)
          sub.textContent = sub.textContent.replace(
            /·.*/,
            `· ${dn}/${wf.length} complete`,
          );
        toast(s.done ? "Step completed" : "Step unchecked", "inf");
      }
      function setTopicSt(tid, st) {
        const t = D.topics.find((x) => x.id == tid);
        if (t) {
          t.status = st;
          toast(`Topic marked ${st}`, "inf");
          CM();
        }
      }

      // ═══════════════════════════════════════════════════════════════
      // MATERIALS
      // ═══════════════════════════════════════════════════════════════
      let mSrch = "",
        mTy = "",
        mCid = "";
      function pgMat() {
        setTop(
          "Materials",
          "Learning material tracking by type",
          `<button class="btn bg bsm" onclick="pgMat()"><i class="bi bi-arrow-clockwise"></i> Refresh</button><button class="btn bp" onclick="openMF()"><i class="bi bi-plus-lg"></i> Add Material</button>`,
        );
        let list = [...D.materials];
        if (mCid) list = list.filter((m) => m.cid == mCid);
        if (mTy) list = list.filter((m) => m.mtype === mTy);
        if (mSrch)
          list = list.filter(
            (m) =>
              (MAT[m.mtype] || "")
                .toLowerCase()
                .includes(mSrch.toLowerCase()) ||
              (tname(m.tid) || "").toLowerCase().includes(mSrch.toLowerCase()),
          );
        const totR = D.materials.reduce((a, m) => a + m.recv, 0),
          totU = D.materials.reduce((a, m) => a + m.upl, 0);
        const co = allowedCourses()
          .map(
            (c) =>
              `<option value="${c.id}" ${mCid == c.id ? "selected" : ""}>${c.num}</option>`,
          )
          .join("");
        const tyO = Object.entries(MAT)
          .map(
            ([v, l]) =>
              `<option value="${v}" ${mTy === v ? "selected" : ""}>${l}</option>`,
          )
          .join("");
        setC(`
  <div class="sg">
    <div class="sc" style="--ca:var(--accent);--ci:var(--ag)"><div class="sc-icon"><i class="bi bi-folder2-open"></i></div><div class="sc-val">${D.materials.length}</div><div class="sc-lbl">Total Entries</div></div>
    <div class="sc" style="--ca:var(--green);--ci:rgba(34,211,160,.12)"><div class="sc-icon"><i class="bi bi-cloud-upload"></i></div><div class="sc-val">${totU}</div><div class="sc-lbl">Total Uploaded</div></div>
    <div class="sc" style="--ca:var(--yellow);--ci:rgba(245,202,66,.12)"><div class="sc-icon"><i class="bi bi-inbox"></i></div><div class="sc-val">${totR}</div><div class="sc-lbl">Total Received</div></div>
    <div class="sc" style="--ca:var(--red);--ci:rgba(240,81,108,.12)"><div class="sc-icon"><i class="bi bi-hourglass-split"></i></div><div class="sc-val">${totR - totU}</div><div class="sc-lbl">Total Pending</div></div>
  </div>
  <div class="tb">
    <div class="sbw"><i class="bi bi-search"></i><input type="text" placeholder="Search materials…" value="${mSrch}" oninput="mSrch=this.value;pgMat()"></div>
    <select class="fsel" onchange="mCid=this.value;pgMat()"><option value="">All Courses</option>${co}</select>
    <select class="fsel" onchange="mTy=this.value;pgMat()"><option value="">All Types</option>${tyO}</select>
  </div>
  ${
    !list.length
      ? `<div class="es"><div class="es-i"><i class="bi bi-folder2"></i></div><div class="es-t">No materials found</div><div class="es-s">Add material tracking entries.</div><button class="btn bp" onclick="openMF()"><i class="bi bi-plus-lg"></i> Add Material</button></div>`
      : `<div class="tw"><table class="ct"><thead><tr><th>Type</th><th>Course</th><th>Topic</th><th>Received</th><th>Uploaded</th><th>Pending</th><th>Progress</th><th>Notes</th><th></th></tr></thead>
  <tbody>${list
    .map((m) => {
      const pend = m.recv - m.upl,
        pct = m.recv > 0 ? Math.round((m.upl / m.recv) * 100) : 0;
      const st =
        pend === 0 && m.recv > 0 ? "complete" : pct > 0 ? "partial" : "pending";
      return `<tr><td><span class="mtype">${MAT[m.mtype] || m.mtype}</span></td>
      <td><span class="mono tx-a" style="font-size:11.5px">${D.courses.find((c) => c.id === m.cid)?.num || "—"}</span></td>
      <td style="font-size:12.5px;color:var(--text2)">${m.tid ? tname(m.tid) : "(Course-level)"}</td>
      <td style="font-weight:700">${m.recv}</td>
      <td style="font-weight:700;color:var(--green)">${m.upl}</td>
      <td style="font-weight:700;color:${pend > 0 ? "var(--red)" : "var(--green)"}">${pend}</td>
      <td style="min-width:120px">
        <div class="pb-lbl" style="margin-bottom:1px"><span>${badge(st)}</span><span>${pct}%</span></div>
        <div class="pb-bg" style="height:4px"><div class="pb-fill" style="width:${pct}%;background:${pct === 100 ? "var(--green)" : pct > 0 ? "var(--accent)" : "var(--border2)"}"></div></div>
      </td>
      <td style="font-size:12px;color:var(--text3);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${m.notes || "—"}</td>
      <td><div style="display:flex;gap:4px;justify-content:flex-end">
        <button class="btn bg bsm bic" onclick="openMF(${m.id})"><i class="bi bi-pencil"></i></button>
        <button class="btn bg bsm bic" onclick="delM(${m.id})"><i class="bi bi-trash3" style="color:var(--red)"></i></button>
      </div></td>
    </tr>`;
    })
    .join("")}</tbody></table></div>`
  }`);
      }

      function openMF(id) {
        const m = id ? D.materials.find((x) => x.id == id) : null;
        const co = allowedCourses()
          .map(
            (c) =>
              `<option value="${c.id}" ${m?.cid == c.id ? "selected" : ""}>${c.num} — ${c.title}</option>`,
          )
          .join("");
        const to = D.topics
          .map(
            (t) =>
              `<option value="${t.id}" ${m?.tid == t.id ? "selected" : ""}>${tname(t.id)}</option>`,
          )
          .join("");
        const tyO = Object.entries(MAT)
          .map(
            ([v, l]) =>
              `<option value="${v}" ${(m?.mtype || "mcq") === v ? "selected" : ""}>${l}</option>`,
          )
          .join("");
        OM({
          icon: "bi-folder2-open",
          title: m ? "Edit Material" : "Add Material",
          sub: "Track received vs uploaded counts. Pending = Received − Uploaded.",
          save: `saveMF(${id || "null"})`,
          body: `<div class="fr">
      <div class="fg"><label class="fl">Course <span>*</span></label><select class="fsi" id="mf-c"><option value="">— Select —</option>${co}</select></div>
      <div class="fg"><label class="fl">Topic (optional)</label><select class="fsi" id="mf-tp"><option value="">— Course-level —</option>${to}</select></div>
    </div>
    <div class="fg"><label class="fl">Material Type <span>*</span></label><select class="fsi" id="mf-ty">${tyO}</select></div>
    <div class="fr">
      <div class="fg"><label class="fl">Received Count <span>*</span></label><input type="number" class="fi" id="mf-r" value="${m?.recv || 0}" min="0" oninput="updPend()"></div>
      <div class="fg"><label class="fl">Uploaded Count <span>*</span></label><input type="number" class="fi" id="mf-u" value="${m?.upl || 0}" min="0" oninput="updPend()"></div>
    </div>
    <div class="fg"><label class="fl">Pending (auto-calculated)</label><input class="fi" id="mf-p" disabled style="opacity:.6;font-weight:700;color:var(--yellow)" value="${(m?.recv || 0) - (m?.upl || 0)}"></div>
    <div class="fg"><label class="fl">Notes</label><textarea class="fta" id="mf-n" style="min-height:50px">${m?.notes || ""}</textarea></div>`,
        });
        if (m) {
          setTimeout(() => {
            if (gi("mf-c")) gi("mf-c").value = m.cid;
            if (gi("mf-tp")) gi("mf-tp").value = m.tid || "";
          }, 50);
        }
      }
      function updPend() {
        const r = parseInt(gi("mf-r")?.value) || 0,
          u = parseInt(gi("mf-u")?.value) || 0;
        const p = gi("mf-p");
        if (p) p.value = Math.max(0, r - u);
      }
      function saveMF(id) {
        const c = parseInt(sV("mf-c")),
          ty = sV("mf-ty");
        if (!c || !ty) {
          toast("Course and type required", "err");
          return;
        }
        const recv = parseInt(fV("mf-r")) || 0,
          upl = Math.min(parseInt(fV("mf-u")) || 0, recv);
        if (id) {
          const m = D.materials.find((x) => x.id == id);
          Object.assign(m, {
            cid: c,
            tid: parseInt(sV("mf-tp")) || null,
            mtype: ty,
            recv,
            upl,
            notes: fV("mf-n") || null,
          });
          toast("Updated");
        } else {
          D.materials.push({
            id: uid(),
            cid: c,
            tid: parseInt(sV("mf-tp")) || null,
            mtype: ty,
            recv,
            upl,
            notes: fV("mf-n") || null,
          });
          toast("Material entry added");
        }
        CM();
        pgMat();
      }
      function delM(id) {
        ODM("Delete this material entry?", () => {
          D.materials = D.materials.filter((m) => m.id != id);
          toast("Deleted");
          pgMat();
        });
      }

      // ═══════════════════════════════════════════════════════════════
      // CONTRACTS
      // ═══════════════════════════════════════════════════════════════
      let ctSrch = "",
        ctSt = "";
      function pgCnt() {
        if (!hasRole("accounts")) {
          toast("Access denied", "err");
          return;
        }
        const totV = D.contracts.reduce((a, c) => a + c.amt, 0);
        setTop(
          "Contracts",
          "Faculty contract management",
          `<button class="btn bg bsm" onclick="pgCnt()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>${hasRole("admin") ? `<button class="btn bp" onclick="openCtF()"><i class="bi bi-plus-lg"></i> New Contract</button>` : ""}`,
        );
        let list = [...D.contracts];
        if (ctSrch)
          list = list.filter(
            (c) =>
              c.title.toLowerCase().includes(ctSrch.toLowerCase()) ||
              c.num.toLowerCase().includes(ctSrch.toLowerCase()),
          );
        if (ctSt) list = list.filter((c) => c.status === ctSt);
        setC(`
  <div class="sg">
    <div class="sc" style="--ca:var(--accent);--ci:var(--ag)"><div class="sc-icon"><i class="bi bi-file-earmark-text-fill"></i></div><div class="sc-val">${D.contracts.length}</div><div class="sc-lbl">Total</div></div>
    <div class="sc" style="--ca:var(--green);--ci:rgba(34,211,160,.12)"><div class="sc-icon"><i class="bi bi-file-earmark-check-fill"></i></div><div class="sc-val">${D.contracts.filter((c) => c.status === "active").length}</div><div class="sc-lbl">Active</div></div>
    <div class="sc" style="--ca:var(--yellow);--ci:rgba(245,202,66,.12)"><div class="sc-icon"><i class="bi bi-currency-rupee"></i></div><div class="sc-val" style="font-size:16px">${fmtC(totV)}</div><div class="sc-lbl">Total Value</div></div>
    <div class="sc" style="--ca:var(--orange);--ci:rgba(245,146,78,.12)"><div class="sc-icon"><i class="bi bi-currency-rupee"></i></div><div class="sc-val" style="font-size:16px">${fmtC(D.contracts.filter((c) => c.status === "active").reduce((a, c) => a + c.amt, 0))}</div><div class="sc-lbl">Active Value</div></div>
  </div>
  <div class="tb">
    <div class="sbw"><i class="bi bi-search"></i><input type="text" placeholder="Search contracts…" value="${ctSrch}" oninput="ctSrch=this.value;pgCnt()"></div>
    <select class="fsel" onchange="ctSt=this.value;pgCnt()"><option value="">All Status</option>${["draft", "sent", "signed", "active", "completed", "cancelled"].map((s) => `<option value="${s}" ${ctSt === s ? "selected" : ""}>${s}</option>`).join("")}</select>
  </div>
  ${
    !list.length
      ? `<div class="es"><div class="es-i"><i class="bi bi-file-earmark-text"></i></div><div class="es-t">No contracts found</div></div>`
      : `<div class="tw"><table class="ct"><thead><tr><th>Number</th><th>Title</th><th>Faculty</th><th>Course</th><th>Amount</th><th>Paid</th><th>Status</th><th>Period</th><th></th></tr></thead>
  <tbody>${list
    .map((c) => {
      const pa = D.payments
        .filter((p) => p.ctid === c.id && p.status === "paid")
        .reduce((a, p) => a + p.amt, 0);
      const pct = c.amt ? Math.round((pa / c.amt) * 100) : 0;
      return `<tr><td><span class="mono tx-a" style="font-size:11.5px">${c.num}</span></td>
      <td style="max-width:190px"><div style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${c.title}</div></td>
      <td style="font-size:13px;color:var(--text2)">${fname(c.fid)}</td>
      <td style="font-size:12px;color:var(--text3)">${c.cid ? D.courses.find((x) => x.id === c.cid)?.num || "—" : "—"}</td>
      <td style="font-weight:700">${fmtC(c.amt)}</td>
      <td><span style="color:var(--green)">${fmtC(pa)}</span> <span style="font-size:11px;color:var(--text3)">(${pct}%)</span></td>
      <td>${badge(c.status)}</td>
      <td style="font-size:12px;color:var(--text3)">${c.sd}${c.ed ? " → " + c.ed : ""}</td>
      <td>${hasRole("admin") ? `<div style="display:flex;gap:4px;justify-content:flex-end"><button class="btn bg bsm bic" onclick="openCtF(${c.id})"><i class="bi bi-pencil"></i></button><button class="btn bg bsm bic" onclick="delCt(${c.id},'${c.num}')"><i class="bi bi-trash3" style="color:var(--red)"></i></button></div>` : ""}</td>
    </tr>`;
    })
    .join("")}</tbody></table></div>`
  }`);
      }

      function openCtF(id) {
        const c = id ? D.contracts.find((x) => x.id == id) : null;
        const fo = D.faculty
          .filter((f) => f.status === "active")
          .map(
            (f) =>
              `<option value="${f.id}" ${c?.fid == f.id ? "selected" : ""}>${f.name}</option>`,
          )
          .join("");
        const co = D.courses
          .map(
            (x) =>
              `<option value="${x.id}" ${c?.cid == x.id ? "selected" : ""}>${x.num} — ${x.title}</option>`,
          )
          .join("");
        OM({
          icon: "bi-file-earmark-text",
          title: c ? "Edit Contract" : "New Contract",
          sub: "Contract details",
          save: `saveCtF(${id || "null"})`,
          lg: true,
          body: `<div class="fg"><label class="fl">Title <span>*</span></label><input class="fi" id="ctf-t" value="${esc(c?.title || "")}"></div>
    <div class="fr"><div class="fg"><label class="fl">Faculty <span>*</span></label><select class="fsi" id="ctf-f"><option value="">—</option>${fo}</select></div>
    <div class="fg"><label class="fl">Course</label><select class="fsi" id="ctf-c"><option value="">— None —</option>${co}</select></div></div>
    <div class="fr"><div class="fg"><label class="fl">Total Amount (₹) <span>*</span></label><input type="number" class="fi" id="ctf-a" value="${c?.amt || ""}"></div>
    <div class="fg"><label class="fl">Status</label><select class="fsi" id="ctf-st">${["draft", "sent", "signed", "active", "completed", "cancelled"].map((s) => `<option value="${s}" ${(c?.status || "draft") === s ? "selected" : ""}>${s}</option>`).join("")}</select></div></div>
    <div class="fr"><div class="fg"><label class="fl">Start Date <span>*</span></label><input type="date" class="fi" id="ctf-sd" value="${c?.sd || ""}"></div>
    <div class="fg"><label class="fl">End Date</label><input type="date" class="fi" id="ctf-ed" value="${c?.ed || ""}"></div></div>
    <div class="fg"><label class="fl">Terms</label><textarea class="fta" id="ctf-tm">${c?.terms || ""}</textarea></div>`,
        });
        if (c) {
          setTimeout(() => {
            if (gi("ctf-f")) gi("ctf-f").value = c.fid;
            if (gi("ctf-c")) gi("ctf-c").value = c.cid || "";
          }, 50);
        }
      }
      function saveCtF(id) {
        const t = fV("ctf-t"),
          f = parseInt(sV("ctf-f")),
          sd = fV("ctf-sd");
        if (!t || !f || !sd) {
          toast("Title, faculty & start date required", "err");
          return;
        }
        if (id) {
          const c = D.contracts.find((x) => x.id == id);
          Object.assign(c, {
            title: t,
            fid: f,
            cid: parseInt(sV("ctf-c")) || null,
            amt: parseFloat(fV("ctf-a")) || 0,
            status: sV("ctf-st"),
            sd,
            ed: fV("ctf-ed") || null,
            terms: fV("ctf-tm") || null,
          });
          toast("Contract updated");
        } else {
          const num =
            "CNT-" + new Date().getFullYear() + "-" + String(uid()).slice(-3);
          D.contracts.push({
            id: uid(),
            fid: f,
            cid: parseInt(sV("ctf-c")) || null,
            num,
            title: t,
            sd,
            ed: fV("ctf-ed") || null,
            amt: parseFloat(fV("ctf-a")) || 0,
            status: sV("ctf-st"),
            terms: fV("ctf-tm") || null,
            signed: null,
          });
          toast("Contract created");
        }
        CM();
        pgCnt();
      }
      function delCt(id, num) {
        ODM(`Delete contract "${num}"?`, () => {
          D.contracts = D.contracts.filter((c) => c.id != id);
          toast("Deleted");
          pgCnt();
        });
      }

      // ═══════════════════════════════════════════════════════════════
      // PAYMENTS
      // ═══════════════════════════════════════════════════════════════
      let pySrch = "",
        pySt = "",
        pyFid = "";
      function pgPay() {
        if (!hasRole("accounts")) {
          toast("Access denied", "err");
          return;
        }
        const paid = D.payments
          .filter((p) => p.status === "paid")
          .reduce((a, p) => a + p.amt, 0);
        const pend = D.payments
          .filter((p) => p.status === "pending")
          .reduce((a, p) => a + p.amt, 0);
        setTop(
          "Payments",
          "Invoice & payment tracking",
          `<button class="btn bg bsm" onclick="pgPay()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>${hasRole("admin") ? `<button class="btn bp" onclick="openPF()"><i class="bi bi-plus-lg"></i> New Payment</button>` : ""}`,
        );
        let list = [...D.payments];
        if (pySrch)
          list = list.filter((p) =>
            p.inv.toLowerCase().includes(pySrch.toLowerCase()),
          );
        if (pySt) list = list.filter((p) => p.status === pySt);
        if (pyFid) list = list.filter((p) => p.fid == pyFid);
        const fo = D.faculty
          .filter((f) => f.status === "active")
          .map(
            (f) =>
              `<option value="${f.id}" ${pyFid == f.id ? "selected" : ""}>${f.name}</option>`,
          )
          .join("");
        setC(`
  <div class="sg">
    <div class="sc" style="--ca:var(--green);--ci:rgba(34,211,160,.12)"><div class="sc-icon"><i class="bi bi-check-circle-fill"></i></div><div class="sc-val" style="font-size:16px">${fmtC(paid)}</div><div class="sc-lbl">Total Paid</div></div>
    <div class="sc" style="--ca:var(--yellow);--ci:rgba(245,202,66,.12)"><div class="sc-icon"><i class="bi bi-hourglass-split"></i></div><div class="sc-val" style="font-size:16px">${fmtC(pend)}</div><div class="sc-lbl">Pending</div></div>
    <div class="sc" style="--ca:var(--orange);--ci:rgba(245,146,78,.12)"><div class="sc-icon"><i class="bi bi-receipt"></i></div><div class="sc-val">${D.payments.length}</div><div class="sc-lbl">Total Invoices</div></div>
    <div class="sc" style="--ca:var(--accent);--ci:var(--ag)"><div class="sc-icon"><i class="bi bi-arrow-repeat"></i></div><div class="sc-val">${D.payments.filter((p) => p.status === "processing").length}</div><div class="sc-lbl">Processing</div></div>
  </div>
  <div class="tb">
    <div class="sbw"><i class="bi bi-search"></i><input type="text" placeholder="Search invoice…" value="${pySrch}" oninput="pySrch=this.value;pgPay()"></div>
    <select class="fsel" onchange="pyFid=this.value;pgPay()"><option value="">All Faculty</option>${fo}</select>
    <select class="fsel" onchange="pySt=this.value;pgPay()"><option value="">All Status</option>${["pending", "processing", "paid", "failed", "refunded"].map((s) => `<option value="${s}" ${pySt === s ? "selected" : ""}>${s}</option>`).join("")}</select>
  </div>
  ${
    !list.length
      ? `<div class="es"><div class="es-i"><i class="bi bi-credit-card-2-front"></i></div><div class="es-t">No payments found</div></div>`
      : `<div class="tw"><table class="ct"><thead><tr><th>Invoice #</th><th>Faculty</th><th>Amount</th><th>Type</th><th>Status</th><th>Due</th><th>Paid On</th><th></th></tr></thead>
  <tbody>${list
    .map(
      (p) => `<tr>
    <td><span class="mono tx-a" style="font-size:11.5px">${p.inv}</span></td>
    <td style="font-size:13px;color:var(--text2)">${fname(p.fid)}</td>
    <td><span style="font-size:15px;font-weight:700;color:${p.status === "paid" ? "var(--green)" : p.status === "pending" ? "var(--yellow)" : "var(--text)"}">${fmtC(p.amt)}</span></td>
    <td>${badge(p.ptype, p.ptype)}</td><td>${badge(p.status)}</td>
    <td style="font-size:12.5px;color:var(--text3)">${fmtD(p.due)}</td>
    <td style="font-size:12.5px;color:var(--green)">${p.paid ? fmtD(p.paid) : "—"}</td>
    <td>${hasRole("admin") ? `<div style="display:flex;gap:4px;justify-content:flex-end">${p.status === "pending" ? `<button class="btn bs bsm bic" onclick="markPaid(${p.id})" title="Mark Paid"><i class="bi bi-check2-all"></i></button>` : ""}<button class="btn bg bsm bic" onclick="openPF(${p.id})"><i class="bi bi-pencil"></i></button><button class="btn bg bsm bic" onclick="delP(${p.id},'${p.inv}')"><i class="bi bi-trash3" style="color:var(--red)"></i></button></div>` : ""}</td>
  </tr>`,
    )
    .join("")}</tbody></table></div>`
  }`);
      }
      function markPaid(id) {
        const p = D.payments.find((x) => x.id == id);
        if (p) {
          p.status = "paid";
          p.paid = today();
          toast("Marked as paid");
          pgPay();
        }
      }
      function openPF(id) {
        const p = id ? D.payments.find((x) => x.id == id) : null;
        const fo = D.faculty
          .map(
            (f) =>
              `<option value="${f.id}" ${p?.fid == f.id ? "selected" : ""}>${f.name}</option>`,
          )
          .join("");
        const co = D.contracts
          .map(
            (c) =>
              `<option value="${c.id}" ${p?.ctid == c.id ? "selected" : ""}>${c.num}</option>`,
          )
          .join("");
        OM({
          icon: "bi-credit-card-2-front",
          title: p ? "Edit Payment" : "New Payment",
          sub: "Invoice details",
          save: `savePF(${id || "null"})`,
          body: `<div class="fr"><div class="fg"><label class="fl">Faculty <span>*</span></label><select class="fsi" id="pf-f"><option value="">—</option>${fo}</select></div>
    <div class="fg"><label class="fl">Contract</label><select class="fsi" id="pf-ct"><option value="">—</option>${co}</select></div></div>
    <div class="fr"><div class="fg"><label class="fl">Amount (₹) <span>*</span></label><input type="number" class="fi" id="pf-a" value="${p?.amt || ""}"></div>
    <div class="fg"><label class="fl">Type</label><select class="fsi" id="pf-pt">${["advance", "milestone", "final", "bonus"].map((v) => `<option value="${v}" ${(p?.ptype || "milestone") === v ? "selected" : ""}>${v}</option>`).join("")}</select></div></div>
    <div class="fr"><div class="fg"><label class="fl">Status</label><select class="fsi" id="pf-st">${["pending", "processing", "paid", "failed", "refunded"].map((v) => `<option value="${v}" ${(p?.status || "pending") === v ? "selected" : ""}>${v}</option>`).join("")}</select></div>
    <div class="fg"><label class="fl">Method</label><input class="fi" id="pf-pm" value="${esc(p?.method || "")}"></div></div>
    <div class="fr"><div class="fg"><label class="fl">Due Date</label><input type="date" class="fi" id="pf-due" value="${p?.due || ""}"></div>
    <div class="fg"><label class="fl">Paid Date</label><input type="date" class="fi" id="pf-pd" value="${p?.paid || ""}"></div></div>`,
        });
        if (p) {
          setTimeout(() => {
            if (gi("pf-f")) gi("pf-f").value = p.fid;
            if (gi("pf-ct")) gi("pf-ct").value = p.ctid || "";
          }, 50);
        }
      }
      function savePF(id) {
        const f = parseInt(sV("pf-f")),
          a = parseFloat(fV("pf-a"));
        if (!f || !a) {
          toast("Faculty & amount required", "err");
          return;
        }
        if (id) {
          const p = D.payments.find((x) => x.id == id);
          Object.assign(p, {
            fid: f,
            ctid: parseInt(sV("pf-ct")) || null,
            amt: a,
            ptype: sV("pf-pt"),
            status: sV("pf-st"),
            method: fV("pf-pm") || null,
            due: fV("pf-due") || null,
            paid: fV("pf-pd") || null,
          });
          toast("Updated");
        } else {
          const inv =
            "INV-" + new Date().getFullYear() + "-" + String(uid()).slice(-3);
          D.payments.push({
            id: uid(),
            ctid: parseInt(sV("pf-ct")) || null,
            fid: f,
            inv,
            amt: a,
            ptype: sV("pf-pt"),
            status: sV("pf-st"),
            due: fV("pf-due") || null,
            paid: fV("pf-pd") || null,
            method: fV("pf-pm") || null,
            txn: null,
          });
          toast("Payment created");
        }
        CM();
        pgPay();
      }
      function delP(id, inv) {
        ODM(`Delete invoice "${inv}"?`, () => {
          D.payments = D.payments.filter((p) => p.id != id);
          toast("Deleted");
          pgPay();
        });
      }

      // ═══════════════════════════════════════════════════════════════
      // FACULTY MASTER
      // ═══════════════════════════════════════════════════════════════
      function pgFac() {
        if (!hasRole("admin")) {
          toast("Access denied", "err");
          return;
        }
        setTop(
          "Faculty",
          "Faculty master directory",
          `<button class="btn bg bsm" onclick="pgFac()"><i class="bi bi-arrow-clockwise"></i> Refresh</button><button class="btn bp" onclick="openFF()"><i class="bi bi-plus-lg"></i> Add Faculty</button>`,
        );
        setC(`
  <div class="sg" style="grid-template-columns:repeat(3,1fr)">
    <div class="sc" style="--ca:var(--accent);--ci:var(--ag)"><div class="sc-icon"><i class="bi bi-person-badge-fill"></i></div><div class="sc-val">${D.faculty.length}</div><div class="sc-lbl">Total Faculty</div></div>
    <div class="sc" style="--ca:var(--green);--ci:rgba(34,211,160,.12)"><div class="sc-icon"><i class="bi bi-person-check-fill"></i></div><div class="sc-val">${D.faculty.filter((f) => f.status === "active").length}</div><div class="sc-lbl">Active</div></div>
    <div class="sc" style="--ca:var(--yellow);--ci:rgba(245,202,66,.12)"><div class="sc-icon"><i class="bi bi-file-earmark-text-fill"></i></div><div class="sc-val">${D.contracts.filter((c) => c.status === "active").length}</div><div class="sc-lbl">Active Contracts</div></div>
  </div>
  <div class="cg">
    ${D.faculty
      .map((f) => {
        const ts = D.topics.filter((t) => t.fid === f.id);
        const cts = D.contracts.filter((c) => c.fid === f.id);
        const paid = D.payments
          .filter((p) => p.fid === f.id && p.status === "paid")
          .reduce((a, p) => a + p.amt, 0);
        const pend = D.payments
          .filter((p) => p.fid === f.id && p.status === "pending")
          .reduce((a, p) => a + p.amt, 0);
        return `<div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:17px;position:relative;overflow:hidden">
        <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--accent),var(--accent2))"></div>
        <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:12px">
          <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:grid;place-items:center;font-size:16px;font-weight:700;color:#fff;flex-shrink:0">${f.name.charAt(0)}</div>
          <div style="flex:1;min-width:0">
            <div style="font-size:14px;font-weight:600;margin-bottom:1px">${f.name}</div>
            <div style="font-size:12px;color:var(--text3)">${f.designation || "—"}</div>
            <div style="font-size:11.5px;color:var(--text3)">${f.city}, ${f.state}</div>
          </div>${badge(f.status)}
        </div>
        <div style="font-size:12px;color:var(--text3);margin-bottom:11px">
          <i class="bi bi-telephone"></i> ${f.mobile || "—"} &nbsp; <i class="bi bi-envelope"></i> ${f.email || "—"}
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:7px;margin-bottom:12px">
          <div style="background:var(--bg3);border-radius:7px;padding:8px;text-align:center"><div style="font-size:17px;font-weight:700;color:var(--accent)">${ts.length}</div><div style="font-size:10px;color:var(--text3)">Lectures</div></div>
          <div style="background:var(--bg3);border-radius:7px;padding:8px;text-align:center"><div style="font-size:17px;font-weight:700;color:var(--green)">${cts.length}</div><div style="font-size:10px;color:var(--text3)">Contracts</div></div>
          <div style="background:var(--bg3);border-radius:7px;padding:8px;text-align:center"><div style="font-size:17px;font-weight:700;color:var(--orange)">${ts.filter((t) => t.status === "completed").length}</div><div style="font-size:10px;color:var(--text3)">Done</div></div>
        </div>
        <div style="display:flex;justify-content:space-between;padding:9px 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border);margin-bottom:11px">
          <div><div style="font-size:10px;color:var(--text3)">Paid</div><div style="font-size:13px;font-weight:700;color:var(--green)">${fmtC(paid)}</div></div>
          <div style="text-align:right"><div style="font-size:10px;color:var(--text3)">Pending</div><div style="font-size:13px;font-weight:700;color:var(--yellow)">${fmtC(pend)}</div></div>
        </div>
        <div style="display:flex;gap:5px"><button class="btn bg bsm" style="flex:1;justify-content:center" onclick="openFF(${f.id})"><i class="bi bi-pencil"></i> Edit</button></div>
      </div>`;
      })
      .join("")}
  </div>`);
      }

      function openFF(id) {
        const f = id ? D.faculty.find((x) => x.id == id) : null;
        OM({
          icon: "bi-person-badge",
          title: f ? "Edit Faculty" : "Add Faculty",
          sub: "Faculty master record (used across courses & topics)",
          save: `saveFF(${id || "null"})`,
          body: `<div class="fr">
      <div class="fg"><label class="fl">Full Name <span>*</span></label><input class="fi" id="ff-n" value="${esc(f?.name || "")}"></div>
      <div class="fg"><label class="fl">Designation</label><input class="fi" id="ff-des" value="${esc(f?.designation || "")}"></div>
    </div>
    <div class="fr">
      <div class="fg"><label class="fl">Mobile</label><input class="fi" id="ff-m" value="${esc(f?.mobile || "")}"></div>
      <div class="fg"><label class="fl">Email</label><input type="email" class="fi" id="ff-e" value="${esc(f?.email || "")}"></div>
    </div>
    <div class="fr">
      <div class="fg"><label class="fl">City</label><input class="fi" id="ff-ci" value="${esc(f?.city || "")}"></div>
      <div class="fg"><label class="fl">State</label><input class="fi" id="ff-st" value="${esc(f?.state || "")}"></div>
    </div>
    <div class="fr">
      <div class="fg"><label class="fl">Country</label><input class="fi" id="ff-co" value="${esc(f?.country || "India")}"></div>
      <div class="fg"><label class="fl">Status</label><select class="fsi" id="ff-s"><option value="active" ${(f?.status || "active") === "active" ? "selected" : ""}>Active</option><option value="inactive" ${f?.status === "inactive" ? "selected" : ""}>Inactive</option></select></div>
    </div>`,
        });
      }
      function saveFF(id) {
        const n = fV("ff-n");
        if (!n) {
          toast("Name required", "err");
          return;
        }
        if (id) {
          const f = D.faculty.find((x) => x.id == id);
          Object.assign(f, {
            name: n,
            designation: fV("ff-des") || null,
            mobile: fV("ff-m") || null,
            email: fV("ff-e") || null,
            city: fV("ff-ci") || null,
            state: fV("ff-st") || null,
            country: fV("ff-co") || "India",
            status: sV("ff-s"),
          });
          toast("Faculty updated");
        } else {
          D.faculty.push({
            id: uid(),
            name: n,
            designation: fV("ff-des") || null,
            mobile: fV("ff-m") || null,
            email: fV("ff-e") || null,
            city: fV("ff-ci") || null,
            state: fV("ff-st") || null,
            country: fV("ff-co") || "India",
            status: sV("ff-s"),
          });
          toast("Faculty added");
        }
        CM();
        pgFac();
      }

      // ═══════════════════════════════════════════════════════════════
      // TASKS
      // ═══════════════════════════════════════════════════════════════
      let tskSt = "",
        tskPr = "";
      function pgTasks() {
        let list = [...D.tasks];
        if (!hasRole("admin")) list = list.filter((t) => t.to === CU.id);
        if (tskSt) list = list.filter((t) => t.status === tskSt);
        if (tskPr) list = list.filter((t) => t.priority === tskPr);
        const openN = D.tasks.filter(
          (t) => t.to === CU.id && t.status !== "completed",
        ).length;
        set("badge-tasks", openN);
        setTop(
          "Tasks",
          "Task management & assignments",
          `<button class="btn bg bsm" onclick="pgTasks()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>${hasRole("admin") ? `<button class="btn bp" onclick="openTskF()"><i class="bi bi-plus-lg"></i> New Task</button>` : ""}`,
        );
        setC(`
  <div class="sg">
    <div class="sc" style="--ca:var(--red);--ci:rgba(240,81,108,.12)"><div class="sc-icon"><i class="bi bi-exclamation-triangle-fill"></i></div><div class="sc-val">${list.filter((t) => t.priority === "urgent").length}</div><div class="sc-lbl">Urgent</div></div>
    <div class="sc" style="--ca:var(--orange);--ci:rgba(245,146,78,.12)"><div class="sc-icon"><i class="bi bi-check2-square"></i></div><div class="sc-val">${list.filter((t) => t.status === "open").length}</div><div class="sc-lbl">Open</div></div>
    <div class="sc" style="--ca:var(--accent);--ci:var(--ag)"><div class="sc-icon"><i class="bi bi-arrow-repeat"></i></div><div class="sc-val">${list.filter((t) => t.status === "in_progress").length}</div><div class="sc-lbl">In Progress</div></div>
    <div class="sc" style="--ca:var(--green);--ci:rgba(34,211,160,.12)"><div class="sc-icon"><i class="bi bi-check-circle-fill"></i></div><div class="sc-val">${list.filter((t) => t.status === "completed").length}</div><div class="sc-lbl">Completed</div></div>
  </div>
  <div class="tb">
    <select class="fsel" onchange="tskSt=this.value;pgTasks()"><option value="">All Status</option>${["open", "in_progress", "completed", "cancelled"].map((s) => `<option value="${s}" ${tskSt === s ? "selected" : ""}>${s.replace("_", " ")}</option>`).join("")}</select>
    <select class="fsel" onchange="tskPr=this.value;pgTasks()"><option value="">All Priority</option>${["urgent", "high", "medium", "low"].map((s) => `<option value="${s}" ${tskPr === s ? "selected" : ""}>${s}</option>`).join("")}</select>
  </div>
  ${
    !list.length
      ? `<div class="es"><div class="es-i"><i class="bi bi-check2-square"></i></div><div class="es-t">No tasks found</div><div class="es-s">Create tasks and assign them to team members.</div>${hasRole("admin") ? `<button class="btn bp" onclick="openTskF()"><i class="bi bi-plus-lg"></i> New Task</button>` : ""}</div>`
      : list
          .sort((a, b) => {
            const po = { urgent: 4, high: 3, medium: 2, low: 1 };
            return (po[b.priority] || 0) - (po[a.priority] || 0);
          })
          .map(
            (t) => `<div class="task-item">
    <div class="tpbar ${t.priority}"></div>
    <div style="flex:1;min-width:0">
      <div style="font-size:13.5px;font-weight:600;margin-bottom:2px">${t.title}</div>
      <div style="font-size:12px;color:var(--text3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${t.desc || "—"}</div>
      <div style="display:flex;gap:10px;margin-top:5px;flex-wrap:wrap">
        <span style="font-size:11.5px;color:var(--text2)"><i class="bi bi-person"></i> ${uname(t.to)}</span>
        <span style="font-size:11.5px;color:var(--text3)"><i class="bi bi-calendar3"></i> Due: ${fmtD(t.due)}</span>
        ${t.module && t.module !== "general" ? `<span style="font-size:11.5px;color:var(--text3)"><i class="bi bi-link-45deg"></i> ${t.module.charAt(0).toUpperCase() + t.module.slice(1)}</span>` : ""}
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:7px;flex-shrink:0">
      ${badge(t.priority, t.priority)} ${badge(t.status.replace("_", " "))}
      ${t.status !== "completed" ? `<button class="btn bs bsm bic" onclick="completeTask(${t.id})" title="Mark Done"><i class="bi bi-check2-all"></i></button>` : ""}
      ${
        hasRole("admin")
          ? `<button class="btn bg bsm bic" onclick="openTskF(${t.id})"><i class="bi bi-pencil"></i></button><button class="btn bg bsm bic" onclick="delTsk(${t.id},'${esc(t.title)}')"><i class="bi bi-trash3" style="color:var(--red)"></i></button>`
          : ""
      }
    </div>
  </div>`,
          )
          .join("")
  }`);
      }

      function completeTask(id) {
        const t = D.tasks.find((x) => x.id == id);
        if (t) {
          t.status = "completed";
          toast("Task completed");
          pgTasks();
        }
      }

      function openTskF(id) {
        const t = id ? D.tasks.find((x) => x.id == id) : null;
        const uo = DEMO_USERS.map(
          (u) =>
            `<option value="${u.id}" ${t?.to == u.id ? "selected" : ""}>${u.name} (${u.role})</option>`,
        ).join("");
        OM({
          icon: "bi-check2-square",
          title: t ? "Edit Task" : "New Task",
          sub: "Assign and track work items",
          save: `saveTskF(${id || "null"})`,
          body: `<div class="fg"><label class="fl">Title <span>*</span></label><input class="fi" id="tskf-t" value="${esc(t?.title || "")}"></div>
    <div class="fg"><label class="fl">Description</label><textarea class="fta" id="tskf-d" style="min-height:55px">${t?.desc || ""}</textarea></div>
    <div class="fr"><div class="fg"><label class="fl">Assign To <span>*</span></label><select class="fsi" id="tskf-to"><option value="">—</option>${uo}</select></div>
    <div class="fg"><label class="fl">Related Module</label><select class="fsi" id="tskf-mod"><option value="general">General</option>${["course", "topic", "material", "contract", "payment"].map((v) => `<option value="${v}" ${t?.module === v ? "selected" : ""}>${v.charAt(0).toUpperCase() + v.slice(1)}</option>`).join("")}</select></div></div>
    <div class="fr"><div class="fg"><label class="fl">Priority</label><select class="fsi" id="tskf-pr">${["urgent", "high", "medium", "low"].map((v) => `<option value="${v}" ${(t?.priority || "medium") === v ? "selected" : ""}>${v}</option>`).join("")}</select></div>
    <div class="fg"><label class="fl">Status</label><select class="fsi" id="tskf-st">${["open", "in_progress", "completed", "cancelled"].map((v) => `<option value="${v}" ${(t?.status || "open") === v ? "selected" : ""}>${v.replace("_", " ")}</option>`).join("")}</select></div></div>
    <div class="fg"><label class="fl">Due Date</label><input type="date" class="fi" id="tskf-due" value="${t?.due || ""}"></div>
    <div class="fg"><label class="fl">Notes</label><textarea class="fta" id="tskf-n" style="min-height:50px">${t?.notes || ""}</textarea></div>`,
        });
        if (t) {
          setTimeout(() => {
            if (gi("tskf-to")) gi("tskf-to").value = t.to || "";
          }, 50);
        }
      }
      function saveTskF(id) {
        const tl = fV("tskf-t"),
          to = parseInt(sV("tskf-to"));
        if (!tl || !to) {
          toast("Title and assignee required", "err");
          return;
        }
        if (id) {
          const t = D.tasks.find((x) => x.id == id);
          Object.assign(t, {
            title: tl,
            desc: fV("tskf-d"),
            to,
            module: sV("tskf-mod"),
            priority: sV("tskf-pr"),
            status: sV("tskf-st"),
            due: fV("tskf-due") || null,
            notes: fV("tskf-n") || null,
          });
          toast("Task updated");
        } else {
          D.tasks.push({
            id: uid(),
            title: tl,
            desc: fV("tskf-d"),
            to,
            by: CU.id,
            module: sV("tskf-mod"),
            rid: null,
            priority: sV("tskf-pr"),
            status: sV("tskf-st"),
            due: fV("tskf-due") || null,
            notes: fV("tskf-n") || null,
          });
          toast("Task created");
        }
        CM();
        pgTasks();
      }
      function delTsk(id, name) {
        ODM(`Delete task "${name}"?`, () => {
          D.tasks = D.tasks.filter((t) => t.id != id);
          toast("Deleted");
          pgTasks();
        });
      }

      // ═══════════════════════════════════════════════════════════════
      // USERS (Admin only)
      // ═══════════════════════════════════════════════════════════════
      function pgUsers() {
        if (!hasRole("admin")) {
          toast("Access denied", "err");
          return;
        }
        setTop(
          "Users",
          "System user management",
          `<button class="btn bg bsm" onclick="pgUsers()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>`,
        );
        setC(`
  <div class="tw"><table class="ct"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead>
  <tbody>${DEMO_USERS.map(
    (u) => `<tr>
    <td><div style="display:flex;align-items:center;gap:10px"><div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:grid;place-items:center;font-size:12px;font-weight:700;color:#fff">${u.name.charAt(0)}</div><strong>${u.name}</strong></div></td>
    <td style="color:var(--text2)">${u.email}</td>
    <td>${badge(u.role, u.role === "owner" ? "active" : u.role === "admin" ? "active_role" : u.role === "accounts" ? "in_progress" : "recorded")}</td>
    <td>${badge("active")}</td>
  </tr>`,
  ).join("")}</tbody></table></div>
  <div style="margin-top:14px;padding:13px 16px;background:var(--surface);border:1px solid var(--border);border-radius:var(--r);font-size:13px;color:var(--text3)">
    <i class="bi bi-info-circle" style="color:var(--accent)"></i> Users are managed in the database. Connect to your PHP backend to add/edit users via the API.
  </div>`);
      }

      // ═══════════════════════════════════════════════════════════════
      // INIT — user is already authenticated via PHP
      // ═══════════════════════════════════════════════════════════════
      const CU_PHP = <?php echo json_encode($user); ?>;
      
      if (CU_PHP) {
          CU = { id: parseInt(CU_PHP.id), name: CU_PHP.name, role: CU_PHP.role, email: CU_PHP.email };

          // Set up sidebar user
          if (gi("sb-av")) gi("sb-av").textContent = CU.name.charAt(0);
          if (gi("sb-name")) gi("sb-name").textContent = CU.name;
          if (gi("sb-role")) gi("sb-role").textContent = CU.role;

          // Role-based nav visibility
          if (hasRole("admin")) {
              const nu = gi("nav-users");
              if (nu) nu.classList.remove("hidden");
          }
          if (!hasRole("accounts")) {
            ["nav-contracts", "nav-payments"].forEach((id) => {
              const n = gi(id);
              if (n && n.closest(".ni")) n.closest(".ni").style.display = "none";
            });
          }
          if (!hasRole("admin")) {
            const fn = gi("nav-faculty");
            if (fn && fn.closest(".ni")) fn.closest(".ni").style.display = "none";
          }
          const courses = allowedCourses();
          const myOpenTasks = D.tasks.filter(
            (t) => t.to === CU.id && t.status !== "completed",
          ).length;
          set("badge-c", courses.length);
          set("badge-tasks", myOpenTasks);
          go("dashboard");
      } else {
          window.location.href = "login.php";
      }
    </script>
  </body>
</html>
