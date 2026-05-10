+++
title = 'Галерея'
slug = 'gallery'
+++
{{< load-photoswipe >}}

{{< rawhtml >}}
<div class="gallery-filters" id="gallery-filters">
    <button class="gf-btn active" data-year="all">Все</button>
    <button class="gf-btn" data-year="2026">2026</button>
    <button class="gf-btn" data-year="2025">2025</button>
    <button class="gf-btn" data-year="2024">2024</button>
    <button class="gf-btn" data-year="2023">2023</button>
    <button class="gf-btn" data-year="2022">2022</button>
    <button class="gf-btn" data-year="2021">2021</button>
</div>

<style>
/* ============================================================
   VARIANT 1 — TIMELINE SLIDER (ACTIVE)
   Year buttons as points on a horizontal timeline.
   Active year = glowing dot. Gives chronological progression feel.
   ============================================================ */
.gallery-filters {
    display: flex;
    align-items: center;
    gap: 0;
    margin: 0 0 2rem 0;
    padding: 1.2rem 0 0.5rem 0;
    position: relative;
    overflow-x: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.15) transparent;
}
/* The horizontal timeline line */
.gallery-filters::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 1rem;
    right: 1rem;
    height: 2px;
    background: linear-gradient(90deg, rgba(255,255,255,0.05), rgba(255,255,255,0.2) 15%, rgba(255,255,255,0.2) 85%, rgba(255,255,255,0.05));
    transform: translateY(-50%);
    z-index: 0;
    pointer-events: none;
}
.gf-btn {
    position: relative;
    z-index: 1;
    background: transparent;
    border: none;
    padding: 22px 14px 8px 14px;
    font-size: 0.78rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    font-family: inherit;
    line-height: 1;
    white-space: nowrap;
    flex-shrink: 0;
}
/* Dot marker on the timeline */
.gf-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 10px;
    height: 10px;
    background: rgba(255, 255, 255, 0.25);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    margin-top: -4px;
}
.gf-btn:hover {
    color: rgba(255, 255, 255, 0.85);
}
.gf-btn:hover::before {
    background: rgba(255, 255, 255, 0.5);
    width: 13px;
    height: 13px;
    box-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
}
.gf-btn.active {
    color: #fff;
    font-weight: 600;
}
.gf-btn.active::before {
    width: 16px;
    height: 16px;
    background: rgba(130, 200, 255, 0.9);
    box-shadow: 0 0 14px rgba(130, 200, 255, 0.7), 0 0 28px rgba(130, 200, 255, 0.3);
    animation: timeline-pulse 2s ease-in-out infinite;
}
/* "Vse" button — slightly different: a ring instead of a dot */
.gf-btn[data-year="all"]::before {
    background: transparent;
    border: 2px solid rgba(255, 255, 255, 0.3);
    width: 14px;
    height: 14px;
}
.gf-btn[data-year="all"].active::before {
    border-color: rgba(130, 200, 255, 0.9);
    background: rgba(130, 200, 255, 0.15);
    width: 18px;
    height: 18px;
    box-shadow: 0 0 14px rgba(130, 200, 255, 0.6), 0 0 28px rgba(130, 200, 255, 0.25);
}
@keyframes timeline-pulse {
    0%, 100% { box-shadow: 0 0 14px rgba(130, 200, 255, 0.7), 0 0 28px rgba(130, 200, 255, 0.3); }
    50% { box-shadow: 0 0 20px rgba(130, 200, 255, 0.9), 0 0 40px rgba(130, 200, 255, 0.4); }
}
@media (max-width: 480px) {
    .gallery-filters {
        padding: 1rem 0 0.3rem 0;
    }
    .gf-btn {
        padding: 20px 10px 6px 10px;
        font-size: 0.72rem;
    }
}


/* ============================================================
   VARIANT 2 — MATERIAL TABS WITH SLIDING UNDERLINE (COMMENTED OUT)
   Clean minimal tabs. Active tab has a smooth sliding underline.
   To activate: uncomment this block and comment out Variant 1 above.
   ============================================================

.gallery-filters {
    display: flex;
    align-items: stretch;
    gap: 0;
    margin: 0 0 1.8rem 0;
    padding: 0;
    position: relative;
    overflow-x: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.15) transparent;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.gf-btn {
    position: relative;
    background: transparent;
    border: none;
    border-radius: 0;
    padding: 12px 20px 14px 20px;
    font-size: 0.85rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: color 0.3s ease;
    font-family: inherit;
    line-height: 1;
    white-space: nowrap;
    flex-shrink: 0;
}
.gf-btn::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2.5px;
    background: linear-gradient(90deg, #82c8ff, #a78bfa);
    border-radius: 2px;
    transform: translateX(-50%);
    transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.gf-btn:hover {
    color: rgba(255, 255, 255, 0.8);
}
.gf-btn:hover::after {
    width: 40%;
}
.gf-btn.active {
    color: #fff;
    font-weight: 600;
}
.gf-btn.active::after {
    width: 70%;
    background: linear-gradient(90deg, #82c8ff, #a78bfa);
    box-shadow: 0 1px 8px rgba(130, 200, 255, 0.4);
}
.gf-btn[data-year="all"] {
    letter-spacing: 0.04em;
    text-transform: uppercase;
    font-size: 0.75rem;
}
.gf-btn[data-year="all"].active::after {
    width: 85%;
    background: linear-gradient(90deg, #a78bfa, #f0abfc);
}
@media (max-width: 480px) {
    .gf-btn {
        padding: 10px 14px 12px 14px;
        font-size: 0.78rem;
    }
}

   END VARIANT 2 */


/* ============================================================
   VARIANT 3 — GRADIENT CHIPS WITH SEASONAL ICONS (COMMENTED OUT)
   Each year gets a unique color gradient and seasonal emoji.
   Active chip has a glowing animated border.
   To activate: uncomment this block and comment out Variant 1 above.
   ============================================================

.gallery-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
    margin: 0 0 1.8rem 0;
    padding: 0;
}
.gf-btn {
    position: relative;
    background: rgba(255, 255, 255, 0.04);
    border: 1.5px solid rgba(255, 255, 255, 0.12);
    border-radius: 28px;
    padding: 9px 18px 9px 14px;
    font-size: 0.82rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.7);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-family: inherit;
    line-height: 1;
    overflow: hidden;
    isolation: isolate;
}
.gf-btn::before {
    margin-right: 5px;
    font-size: 0.9em;
}
.gf-btn[data-year="all"]::before { content: '\2728'; }
.gf-btn[data-year="2026"]::before { content: '\2744\FE0F'; }
.gf-btn[data-year="2025"]::before { content: '\2600\FE0F'; }
.gf-btn[data-year="2024"]::before { content: '\1F343'; }
.gf-btn[data-year="2023"]::before { content: '\1F30A'; }
.gf-btn[data-year="2022"]::before { content: '\26F0\FE0F'; }
.gf-btn[data-year="2021"]::before { content: '\1F308'; }
.gf-btn[data-year="all"] {
    background: linear-gradient(135deg, rgba(167, 139, 250, 0.15), rgba(130, 200, 255, 0.15));
    border-color: rgba(167, 139, 250, 0.3);
}
.gf-btn[data-year="2026"] {
    background: linear-gradient(135deg, rgba(130, 200, 255, 0.1), rgba(200, 230, 255, 0.08));
    border-color: rgba(130, 200, 255, 0.2);
}
.gf-btn[data-year="2025"] {
    background: linear-gradient(135deg, rgba(255, 200, 50, 0.1), rgba(255, 150, 50, 0.08));
    border-color: rgba(255, 200, 50, 0.2);
}
.gf-btn[data-year="2024"] {
    background: linear-gradient(135deg, rgba(100, 200, 100, 0.1), rgba(180, 220, 80, 0.08));
    border-color: rgba(100, 200, 100, 0.2);
}
.gf-btn[data-year="2023"] {
    background: linear-gradient(135deg, rgba(50, 150, 220, 0.1), rgba(80, 200, 200, 0.08));
    border-color: rgba(50, 150, 220, 0.2);
}
.gf-btn[data-year="2022"] {
    background: linear-gradient(135deg, rgba(180, 130, 100, 0.1), rgba(150, 180, 150, 0.08));
    border-color: rgba(180, 130, 100, 0.2);
}
.gf-btn[data-year="2021"] {
    background: linear-gradient(135deg, rgba(200, 100, 150, 0.1), rgba(100, 150, 250, 0.08));
    border-color: rgba(200, 100, 150, 0.2);
}
.gf-btn:hover {
    color: rgba(255, 255, 255, 0.95);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}
.gf-btn.active {
    color: #fff;
    font-weight: 600;
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
    animation: chip-glow 2.5s ease-in-out infinite;
}
.gf-btn[data-year="all"].active { border-color: rgba(167, 139, 250, 0.7); box-shadow: 0 0 12px rgba(167, 139, 250, 0.4); }
.gf-btn[data-year="2026"].active { border-color: rgba(130, 200, 255, 0.7); box-shadow: 0 0 12px rgba(130, 200, 255, 0.4); }
.gf-btn[data-year="2025"].active { border-color: rgba(255, 200, 50, 0.7); box-shadow: 0 0 12px rgba(255, 200, 50, 0.4); }
.gf-btn[data-year="2024"].active { border-color: rgba(100, 200, 100, 0.7); box-shadow: 0 0 12px rgba(100, 200, 100, 0.4); }
.gf-btn[data-year="2023"].active { border-color: rgba(50, 150, 220, 0.7); box-shadow: 0 0 12px rgba(50, 150, 220, 0.4); }
.gf-btn[data-year="2022"].active { border-color: rgba(180, 130, 100, 0.7); box-shadow: 0 0 12px rgba(180, 130, 100, 0.4); }
.gf-btn[data-year="2021"].active { border-color: rgba(200, 100, 150, 0.7); box-shadow: 0 0 12px rgba(200, 100, 150, 0.4); }
@keyframes chip-glow {
    0%, 100% { filter: brightness(1); }
    50% { filter: brightness(1.15); }
}
@media (max-width: 480px) {
    .gf-btn {
        padding: 7px 14px 7px 10px;
        font-size: 0.75rem;
    }
}

   END VARIANT 3 */


/* Shared: hidden gallery items */
.gallery .box.gf-hidden {
    display: none !important;
}
</style>
{{< /rawhtml >}}

{{< gallery caption-effect="fade" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Izmaylovskiy-20260506-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Timiryazevskiy-20260415-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Pantelee-20260408-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Marta-20260402-2.jpg" alt="Весенний пейзаж 2" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Savelevo-20260402-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Zvezda-20260402-2.jpg" alt="Весенний пейзаж 2" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Baykal-20260324-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Serdtse-20260401-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Krasnyybogatyr-20260315-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Generalasnazina-20260402-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Pokrov-20260315-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Shirkov-20260315-2.jpg" alt="Весенний пейзаж 2" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Elochka-20260109-2.jpg" alt="Зимний пейзаж 2" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Vetchi-20260109-1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Yurevpolskiy-20260109-8.jpg" alt="Зимний пейзаж 8" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Panteleevo-20260109-1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Kositskiyles-20260109-1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Shirkovo-20260103-1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Norilsk-20251006-1.webp" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Suzdal-20250925-4.jpg" alt="Осенний пейзаж 4" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Gurevo-20250912-2.jpg" alt="Осенний пейзаж 2" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Vetchi-20250908-1.webp" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Pokrov-20250908-1.webp" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Petushki_20250517_2.jpg" alt="Весенний пейзаж 2" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Rjev_20251005_1.jpg" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Dukyn_20250425_1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Snazin_20250423_1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Bogolubovo_20250419_1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Radiotele_20250406_1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/PicVetchi_20250322_1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Proletar_20250223_1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Gorb_20250304_10.jpg" alt="Весенний пейзаж 10" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Voron_20250324_3.jpg" alt="Весенний пейзаж 3" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/SletRjev_20250215_1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/SletPokrov2_20250208_1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/SletRaek_20250118_1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Bursol2_20250106_8.jpg" alt="Зимний пейзаж 8" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Bursol2_20250106_1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/NG25_20241214_1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/SletKalyaz_20241221_2.jpg" alt="Зимний пейзаж 2" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Bursol_20240923_1.jpg" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Vetchi_20241012_1.jpg" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Klin_20241005_5.jpg" alt="Осенний пейзаж 5" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Bogolub_20240914_1.jpg" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Sletberend_20240907_1.jpg" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Sletlubv_20240831_1.jpg" alt="Летний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Sletkalyaz_20240810_1.jpg" alt="Летний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Sletvas_20240803_1.jpg" alt="Летний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Rzhev_20240727_1.jpg" alt="Летний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Yaropol_20240714_10.jpg" alt="Летний пейзаж 10" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Zelen_20240628_3.jpg" alt="Летний пейзаж 3" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Dronoslet_20240705_4.jpg" alt="Летний пейзаж 4" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Fedor_20240623_4.jpg" alt="Летний пейзаж 4" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Msk-Pet_20240606_3.jpg" alt="Летний пейзаж 3" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Slet_20240526_2.jpg" alt="Весенний пейзаж 2" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Kavkaz_20240429_13.jpg" alt="Весенний пейзаж 13" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Kalyazin-20240603-1.jpg" alt="Летний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Kalyazin-20240330-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Breeze-20240316-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Otkryt-20240309-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Otkryt-20240210-1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Sever-20240203-1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Lager-20240113-1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Serdce-20240120-1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Snegorassvet_20240107_5.jpg" alt="Рассвет 5" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/HappyNew_20231230_1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Moroz rassvet_20231202_8.jpg" alt="Рассвет 8" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Elki-palki_20231216_2.jpg" alt="Зимний пейзаж 2" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Bezdon-20231119-3.jpg" alt="Осенний пейзаж 3" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Dubna-20231118-1.jpg" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Aleksin-20210515-2.jpg" alt="Весенний пейзаж 2" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Altai-20220912-1.jpg" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Apple-20230107-3.jpg" alt="Зимний пейзаж 3" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Belayagora-20220806-1.jpg" alt="Летний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Bykovo-20211107-1.jpg" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Detlager-20210529-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Dino-20220327-7.jpg" alt="Весенний пейзаж 7" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Djipers-20220205-1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Dmitrov-20221016-10.jpg" alt="Осенний пейзаж 10" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Dmitrov_20210328-4.jpg" alt="Весенний пейзаж 4" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Elbrus-20230128-1.jpg" alt="Вид на Эльбрус 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/GES-20220418-3.jpg" alt="Весенний пейзаж 3" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Glubokovo-20220206-3.jpg" alt="Зимний пейзаж 3" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Golf-20220525-10.jpg" alt="Весенний пейзаж 10" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Hrap-20230114-1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Iosifo-20220417-3.jpg" alt="Весенний пейзаж 3" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/KBR-20210928-8.jpg" alt="Осенний пейзаж 8" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Kalininrad-20210913-1.jpg" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Kalyazin-20220123-1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Kalyazin2-20221203-2.jpg" alt="Зимний пейзаж 2" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Kashira-20220605-10.jpg" alt="Летний пейзаж 10" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Kavkaz-20220505-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Kino-20210711-2.jpg" alt="Летний пейзаж 2" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Klin-20220529-3.jpg" alt="Весенний пейзаж 3" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Kolomna-20210505-3.jpg" alt="Весенний пейзаж 3" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Konduki-20210522-6.jpg" alt="Весенний пейзаж 6" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Konduki2-20220716-8.jpg" alt="Летний пейзаж 8" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Kopyto-20210506-3.jpg" alt="Весенний пейзаж 3" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Kurkino-20210914-9.jpg" alt="Осенний пейзаж 9" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Lenivec-20210507-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Morozki-20211019-1.jpg" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Murmansk-20220129-7.jpg" alt="Зимний пейзаж 7" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/NewKBR-20220721-1.jpg" alt="Летний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Ostrova-20220714-2.jpg" alt="Летний пейзаж 2" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Panfil-20211113-1.jpg" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Peremil-20211031-1.jpg" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Piter-20230320-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Podlodka-20220904-2.jpg" alt="Осенний пейзаж 2" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Podolsk-20201229-3.jpg" alt="Зимний пейзаж 3" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Podolsk-20211114-1.jpg" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Pokrov-20230204-1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Posad-20210731-1.jpg" alt="Летний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Rostov-20220219-1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Ryazan-20220221-1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Serdce-20210410-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Sergiev-20220319-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Serpdor-20221008-1.jpg" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Serpuhov-20210428-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Serpuhov-20210509-3.jpg" alt="Весенний пейзаж 3" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Serpzubr-20230108-10.jpg" alt="Зимний пейзаж 10" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Terib-20230311-17.jpg" alt="Весенний пейзаж 17" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Tula-20210619-14.jpg" alt="Летний пейзаж 14" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Tulobl-20230923-1.jpg" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Tver-20220612-8.jpg" alt="Летний пейзаж 8" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Tverobl-20230715-1.jpg" alt="Летний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Univer-20230423-1.jpg" alt="Весенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/VladSuzd-20220814-1.jpg" alt="Летний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Vladimir-20210714-1.jpg" alt="Летний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Volok-20220904-1.jpg" alt="Осенний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Yahroma-20221225-1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Yaroslavl-20220307-8.jpg" alt="Весенний пейзаж 8" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Zimni-20211204-1.jpg" alt="Зимний пейзаж 1" >}}
{{< figure src="https://s3.regru.cloud/sleeptrip-dev/images/Gallery-20260417-1.jpg" alt="Весенний пейзаж 1" >}}
{{< /gallery >}}

{{< rawhtml >}}
<script>
(function() {
    var buttons = document.querySelectorAll('.gf-btn');
    var boxes = document.querySelectorAll('.gallery .box');

    // Extract year from each box's image URL
    boxes.forEach(function(box) {
        var img = box.querySelector('img');
        if (!img) return;
        var src = img.getAttribute('src') || img.getAttribute('data-src') || '';
        var m = src.match(/[_-](20\d{2})\d{4}[_-]/);
        if (m) box.setAttribute('data-year', m[1]);
    });

    buttons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var year = this.getAttribute('data-year');

            // Update active button
            buttons.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');

            // Filter boxes
            boxes.forEach(function(box) {
                if (year === 'all' || box.getAttribute('data-year') === year) {
                    box.classList.remove('gf-hidden');
                } else {
                    box.classList.add('gf-hidden');
                }
            });
        });
    });
})();
</script>
{{< /rawhtml >}}

