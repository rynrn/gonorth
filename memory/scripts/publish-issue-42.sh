#!/usr/bin/env bash
set -euo pipefail
cd /var/www/gonorth
wp eval-file /tmp/apply-post-content-safe.php 447 e5f493af99979b945e7458fe9539d01aae5a2a6c166ec7ac7cfed158b0c5f459 /tmp/issue-42-golan-guide-updated.html
wp eval-file /tmp/apply-post-content-safe.php 202 cfc587f9408f8562a0389e616d4a7a799b4ed796828786ac78e0da526b050a5d /tmp/issue-42-202-new.html
wp eval-file /tmp/apply-post-content-safe.php 204 ce6d9975377f18981bb852200b580e07d59acee8dc5da731e2d00d938c945e40 /tmp/issue-42-204-new.html
wp eval-file /tmp/apply-post-content-safe.php 207 23eaee0eca37681fa085451791c9915653ef6b45b8ab086ababc466c675a816a /tmp/issue-42-207-new.html
wp eval-file /tmp/apply-post-content-safe.php 209 d3107cb96af479475df3a8d739cc13f38bdd606ee7666f9f87456dd6a22b60dc /tmp/issue-42-209-new.html
wp eval-file /tmp/apply-post-content-safe.php 212 fa2dc6b06cd8a7bee262bbb587aa1760fa551cc93bc3e294cfb5f3254d5c42fc /tmp/issue-42-212-new.html
wp eval-file /tmp/apply-post-content-safe.php 213 eb60be3681622b02c18a6a63f6e4e6c89d944c154ce0242b0d0ebf431d0aa613 /tmp/issue-42-213-new.html
wp eval-file /tmp/apply-post-content-safe.php 215 b3ad9c6d544b4cffaa65c73a79e2b7685300d29e7b5a509042cee029172bb375 /tmp/issue-42-215-new.html
wp eval-file /tmp/apply-post-content-safe.php 217 b5c70da38bfdee402c8127996274fd635e29c162bb08133d7faaac377158b025 /tmp/issue-42-217-new.html
wp eval-file /tmp/apply-post-content-safe.php 223 1dde0504797c63a83e4030dfdd46b7c0ab87952775cac47489c80f6dab76b7b9 /tmp/issue-42-223-new.html
wp eval-file /tmp/apply-post-content-safe.php 225 d4a41af3e7c8fd628442f8e089af1aa6bf927c87997f804b6ca0d597a6179364 /tmp/issue-42-225-new.html
wp eval-file /tmp/apply-post-content-safe.php 228 0a3fe12395360f9667a0d063fa02f1310cf9f0979a8d70e36cd338205cc20504 /tmp/issue-42-228-new.html
wp eval-file /tmp/apply-post-content-safe.php 229 ae3077349df7d2f6b7fca8fbe596145037dd9f560030b78c2d63971cdeccddc8 /tmp/issue-42-229-new.html
wp eval-file /tmp/apply-post-content-safe.php 231 b83847dbe6073467532fbbb3e413140830e0107968ff40b5ff74e0cd178107b6 /tmp/issue-42-231-new.html
wp eval-file /tmp/apply-post-content-safe.php 233 54f02de7378294a5596ee08e79bf685f4f33187c66b13329852ba8e4555695c0 /tmp/issue-42-233-new.html
wp eval-file /tmp/apply-post-content-safe.php 339 cee8ff59353fa915bcb54673248f822026ea5e8e56b90c522bfb7f531693ccc9 /tmp/issue-42-339-new.html
wp eval-file /tmp/apply-post-content-safe.php 341 2704fda9a243993a997cc9f0fe15f166e738f11bbda9758724cf7777e510de68 /tmp/issue-42-341-new.html
wp eval-file /tmp/apply-post-content-safe.php 343 fa99cc9fcfc56a40c638e4f4abb98244667502a2a5a9c15bc2804be0b6358fbc /tmp/issue-42-343-new.html
wp eval-file /tmp/apply-post-content-safe.php 425 7ea146880698cda7191f886346c802afb5f02886205763b3cca402efaa63e499 /tmp/issue-42-425-new.html
wp post meta update 447 _yoast_wpseo_title 'לינה וגלמפינג ברמת הגולן: מדריך השוואה | כיוון צפון'
wp post meta update 447 _yoast_wpseo_metadesc 'איך בוחרים לינה או גלמפינג ברמת הגולן? השוואה בין צימר, מלון וארבע אפשרויות גלמפינג, לפי אזור, הרכב, עונה ורמת אבזור.'
wp post meta update 447 _yoast_wpseo_focuskw 'לינה בגולן'
wp rewrite flush
wp cache flush
