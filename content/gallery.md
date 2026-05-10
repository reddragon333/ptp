+++
title = 'Галерея'
slug = 'gallery'
+++
{{< load-photoswipe >}}

{{< rawhtml >}}
<div class="gallery-filters" id="gallery-filters">
    <button class="gf-btn active" data-year="all">ВСЕ</button>
    <span class="gf-separator"></span>
    <button class="gf-btn" data-year="2026">2026</button>
    <button class="gf-btn" data-year="2025">2025</button>
    <button class="gf-btn" data-year="2024">2024</button>
    <button class="gf-btn" data-year="2023">2023</button>
    <button class="gf-btn" data-year="2022">2022</button>
    <button class="gf-btn" data-year="2021">2021</button>
</div>

<style>
/* === Glowing Pulse Timeline with Vertical Line Separator === */
.gallery-filters {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0;
    margin: 0 0 2rem 0;
    padding: 1rem 0;
    position: relative;
    overflow-x: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(0,0,0,0.1) transparent;
}
/* Horizontal timeline line */
.gallery-filters::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 8%;
    right: 8%;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(74,143,200,0.18) 20%, rgba(74,143,200,0.3) 50%, rgba(74,143,200,0.18) 80%, transparent);
    transform: translateY(-50%);
    margin-top: -4px;
    z-index: 0;
    pointer-events: none;
}
/* Vertical line separator between ВСЕ and years */
.gf-separator {
    width: 1px;
    height: 24px;
    background: rgba(74,143,200,0.2);
    flex-shrink: 0;
    margin: 0 4px;
    align-self: center;
    margin-top: -8px;
    z-index: 1;
}
.gf-btn {
    position: relative;
    z-index: 1;
    background: none !important;
    border: none !important;
    box-shadow: none !important;
    color: #8a9aaa;
    font-family: 'Onest', 'Source Sans Pro', Helvetica, sans-serif;
    font-size: 0.78rem;
    font-weight: 500;
    cursor: pointer;
    padding: 0.8rem 1rem 0.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.3rem;
    transition: all 0.35s ease;
    white-space: nowrap;
    flex-shrink: 0;
    text-transform: none;
    letter-spacing: 0;
}
/* Dot above label */
.gf-btn::before {
    content: '';
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid rgba(74,143,200,0.25);
    background: #fff;
    transition: all 0.4s ease;
}
.gf-btn:hover::before {
    border-color: rgba(74,143,200,0.5);
}
.gf-btn:hover {
    color: #4a6a8a;
}
.gf-btn.active {
    color: #1a202c;
    font-weight: 700;
}
.gf-btn.active::before {
    border-color: #4a8fc8;
    background: #4a8fc8;
    box-shadow: 0 0 10px rgba(74,143,200,0.5), 0 0 20px rgba(74,143,200,0.15);
    animation: gf-pulse 2s ease-in-out infinite;
}
@keyframes gf-pulse {
    0%, 100% { box-shadow: 0 0 8px rgba(74,143,200,0.4), 0 0 16px rgba(74,143,200,0.1); }
    50% { box-shadow: 0 0 14px rgba(74,143,200,0.6), 0 0 28px rgba(74,143,200,0.25); }
}
/* Grid of Mini-Dots (2x2) for ВСЕ */
.gf-btn[data-year="all"]::before {
    width: 5px !important;
    height: 5px !important;
    border-radius: 50% !important;
    border: none !important;
    background: rgba(74,143,200,0.35) !important;
    box-shadow: 9px 0 0 0 rgba(74,143,200,0.35),
                0 9px 0 0 rgba(74,143,200,0.35),
                9px 9px 0 0 rgba(74,143,200,0.35) !important;
    transform: translate(-4px, -4px);
    animation: none !important;
}
.gf-btn[data-year="all"]:hover::before {
    background: rgba(74,143,200,0.55);
    box-shadow: 9px 0 0 0 rgba(74,143,200,0.55),
                0 9px 0 0 rgba(74,143,200,0.55),
                9px 9px 0 0 rgba(74,143,200,0.55);
    border-color: transparent;
}
.gf-btn[data-year="all"].active::before {
    background: #4a8fc8 !important;
    box-shadow: 9px 0 0 0 #4a8fc8,
                0 9px 0 0 #4a8fc8,
                9px 9px 0 0 #4a8fc8,
                0 0 10px rgba(74,143,200,0.4),
                9px 0 10px rgba(74,143,200,0.2),
                0 9px 10px rgba(74,143,200,0.2),
                9px 9px 10px rgba(74,143,200,0.2) !important;
    animation: gf-pulse-grid 2s ease-in-out infinite !important;
}
@keyframes gf-pulse-grid {
    0%, 100% { box-shadow: 9px 0 0 0 #4a8fc8, 0 9px 0 0 #4a8fc8, 9px 9px 0 0 #4a8fc8, 0 0 8px rgba(74,143,200,0.35), 9px 0 8px rgba(74,143,200,0.15), 0 9px 8px rgba(74,143,200,0.15), 9px 9px 8px rgba(74,143,200,0.15); }
    50% { box-shadow: 9px 0 0 0 #4a8fc8, 0 9px 0 0 #4a8fc8, 9px 9px 0 0 #4a8fc8, 0 0 14px rgba(74,143,200,0.55), 9px 0 14px rgba(74,143,200,0.25), 0 9px 14px rgba(74,143,200,0.25), 9px 9px 14px rgba(74,143,200,0.25); }
}
@media (max-width: 480px) {
    .gallery-filters { padding: 0.8rem 0 0.3rem 0; }
    .gf-btn { padding: 0.6rem 0.6rem 0.4rem; font-size: 0.72rem; }
    .gf-separator { height: 18px; margin: 0 2px; }
}

/* Hidden gallery items */
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

