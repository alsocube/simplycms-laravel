import './bootstrap';
import imageCompression from 'browser-image-compression';
import { inject } from '@vercel/analytics';
window.imageCompression = imageCompression;
window.inject = inject;