+++
title = 'Галерея'
slug = 'gallery'
+++
{{< load-photoswipe >}}

{{< rawhtml >}}
<div class="gallery-filters" id="gallery-filters">
    <button class="gf-btn active" data-year="all">ВСЕ</button>
    <button class="gf-btn" data-year="2026">2026</button>
    <button class="gf-btn" data-year="2025">2025</button>
    <button class="gf-btn" data-year="2024">2024</button>
    <button class="gf-btn" data-year="2023">2023</button>
    <button class="gf-btn" data-year="2022">2022</button>
    <button class="gf-btn" data-year="2021">2021</button>
</div>

<style>
/* === Fade Typography Filter === */
.gallery-filters {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 0;
    margin: 0 0 2rem 0;
    padding: 0.6rem 0.5rem;
}
.gf-btn {
    font-family: 'Onest', 'Source Sans Pro', Helvetica, sans-serif;
    padding: 8px 9px;
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    font-size: 0.82rem;
    font-weight: 400;
    cursor: pointer;
    transition: all 0.35s ease;
    white-space: nowrap;
    color: #d0d5dc;
}
.gf-btn:hover {
    color: #6a7a8a !important;
}
.gf-btn.active {
    font-size: 1.15rem;
    font-weight: 800;
    color: #1a202c !important;
    padding: 8px 10px;
}
/* ВСЕ — компактнее, с разделителем */
.gf-btn[data-year="all"] {
    font-size: 0.68rem;
    letter-spacing: 0.1em;
    margin-right: 4px;
    padding-right: 12px;
    border-right: 1px solid #e0e4e8 !important;
}
.gf-btn[data-year="all"].active {
    font-size: 0.78rem;
    font-weight: 800;
}
/* Fade levels — applied dynamically via JS */
.gf-btn.fade-1 { color: #a0abb8; font-size: 0.82rem; font-weight: 450; }
.gf-btn.fade-2 { color: #b8c0ca; font-size: 0.78rem; font-weight: 400; }
.gf-btn.fade-3 { color: #cdd3da; font-size: 0.74rem; font-weight: 350; }
.gf-btn.fade-4 { color: #dde1e6; font-size: 0.70rem; font-weight: 300; }
.gf-btn.fade-5 { color: #e8ecf0; font-size: 0.66rem; font-weight: 300; }

@media (max-width: 480px) {
    .gallery-filters { padding: 0.4rem 0; }
    .gf-btn { padding: 6px 6px; font-size: 0.78rem; }
    .gf-btn.active { font-size: 1.05rem; padding: 6px 8px; }
    .gf-btn[data-year="all"] { font-size: 0.62rem; margin-right: 2px; padding-right: 8px; }
    .gf-btn[data-year="all"].active { font-size: 0.72rem; }
    .gf-btn.fade-1 { font-size: 0.78rem; }
    .gf-btn.fade-2 { font-size: 0.74rem; }
    .gf-btn.fade-3 { font-size: 0.70rem; }
    .gf-btn.fade-4 { font-size: 0.66rem; }
    .gf-btn.fade-5 { font-size: 0.62rem; }
}

/* Hidden gallery items */
.gallery .box.gf-hidden {
    display: none !important;
}

/* === Focus Mode: Collapse (#2) === */
/* Header collapses smoothly */
#header {
    transition: max-height 0.5s ease, opacity 0.4s ease, margin 0.5s ease, padding 0.5s ease;
    overflow: hidden;
    max-height: 500px;
}
body.gf-focused #header {
    max-height: 0;
    opacity: 0;
    margin: 0;
    padding: 0;
}
/* Nav collapses */
#nav {
    transition: max-height 0.4s ease, opacity 0.3s ease, padding 0.4s ease;
    overflow: hidden;
    max-height: 200px;
}
body.gf-focused #nav {
    max-height: 0;
    opacity: 0;
    padding: 0;
}
/* Page title shrinks */
.gf-title-target {
    transition: font-size 0.4s ease, padding 0.4s ease, opacity 0.4s ease, max-height 0.4s ease;
    overflow: hidden;
    max-height: 200px;
}
body.gf-focused .gf-title-target {
    font-size: 0.85rem !important;
    padding-top: 6px !important;
    padding-bottom: 2px !important;
    max-height: 30px;
    opacity: 0.6;
}

/* Sticky filter bar in focus mode (especially for mobile) */
body.gf-focused .gallery-filters {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 0.8rem 0.5rem;
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
    var yearButtons = Array.from(buttons).filter(function(b) { return b.getAttribute('data-year') !== 'all'; });
    var boxes = document.querySelectorAll('.gallery .box');

    // Extract year from each box's image URL
    boxes.forEach(function(box) {
        var img = box.querySelector('img');
        if (!img) return;
        var src = img.getAttribute('src') || img.getAttribute('data-src') || '';
        var m = src.match(/[_-](20\d{2})\d{4}[_-]/);
        if (m) box.setAttribute('data-year', m[1]);
    });

    // Dynamic fade: years closer to active are darker, further ones fade out
    function applyFade(activeBtn) {
        var activeIdx = yearButtons.indexOf(activeBtn);
        var isAll = activeBtn.getAttribute('data-year') === 'all';
        
        yearButtons.forEach(function(btn, i) {
            // Remove all fade classes
            btn.classList.remove('fade-1', 'fade-2', 'fade-3', 'fade-4', 'fade-5');
            
            if (btn === activeBtn) return;
            
            if (isAll) {
                // When ВСЕ is active, all years get uniform mid-fade
                btn.classList.add('fade-2');
            } else {
                // Distance-based fade from active year
                var dist = Math.abs(i - activeIdx);
                var level = Math.min(dist, 5);
                if (level > 0) btn.classList.add('fade-' + level);
            }
        });
    }

    // Apply initial fade (ВСЕ is active by default)
    applyFade(buttons[0]);

    // Mark page title for collapse
    var filterEl = document.getElementById('gallery-filters');
    var pageTitle = document.querySelector('h1, header.major h2');
    if (pageTitle) pageTitle.classList.add('gf-title-target');

    // Gallery element for scroll target
    var galleryEl = document.querySelector('.gallery');

    // Track focus state
    var isFocused = false;

    function enterFocus() {
        isFocused = true;
        document.body.classList.add('gf-focused');
    }

    // Always scroll to gallery top (used on every year click)
    function scrollToGallery() {
        if (galleryEl && filterEl) {
            var offset = filterEl.offsetHeight + 16;
            var top = galleryEl.getBoundingClientRect().top + window.pageYOffset - offset;
            window.scrollTo({ top: top, behavior: 'smooth' });
        }
    }

    function exitFocus() {
        if (!isFocused) return;
        isFocused = false;
        document.body.classList.remove('gf-focused');
    }

    // Scroll up detection — restore collapsed elements
    var lastScrollY = window.pageYOffset;
    var scrollUpDistance = 0;
    window.addEventListener('scroll', function() {
        var currentY = window.pageYOffset;
        if (currentY < lastScrollY) {
            scrollUpDistance += (lastScrollY - currentY);
            // After scrolling up 80px, restore everything
            if (scrollUpDistance > 80 && isFocused) {
                exitFocus();
            }
        } else {
            scrollUpDistance = 0;
        }
        // Also restore if scrolled to very top
        if (currentY < 100 && isFocused) {
            exitFocus();
        }
        lastScrollY = currentY;
    }, { passive: true });

    buttons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var year = this.getAttribute('data-year');

            // Update active button
            buttons.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');

            // Apply dynamic fade
            applyFade(this);

            // Focus mode: collapse header + scroll to gallery top
            if (year !== 'all') {
                enterFocus();
                scrollToGallery();
            } else {
                exitFocus();
                // Scroll back to top smoothly
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

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

