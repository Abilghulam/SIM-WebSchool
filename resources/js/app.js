import "./bootstrap";

import Alpine from "alpinejs";
window.Alpine = Alpine;

import { initLoadingUX } from "./ui/loading";
initLoadingUX();

Alpine.start();
