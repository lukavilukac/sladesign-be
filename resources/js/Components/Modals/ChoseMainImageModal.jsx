import { DialogTitle } from '@headlessui/react';
import { CheckCircleIcon, XMarkIcon } from '@heroicons/react/20/solid';
import MainModal from '@/Components/Modals/MainModal.jsx';

const ChoseMainImageModal = ({ images, setOpen, open, selectedImages, handleOnImageClick }) => (
    <MainModal setOpen={setOpen} open={open}>
        <div className="flex items-center justify-between gap-4">
            <DialogTitle className="text-lg font-semibold text-gray-900">Choose images</DialogTitle>
            <button type="button" onClick={() => setOpen(false)} aria-label="Close image picker"
                className="rounded-md p-2 text-gray-500 hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-indigo-600">
                <XMarkIcon className="h-6 w-6" aria-hidden="true" />
            </button>
        </div>
        {images.length === 0 ? (
            <p className="py-10 text-center text-sm text-gray-500">
                No images available. Upload images in the image library first.
            </p>
        ) : (
            <ul className="mt-4 grid max-h-[60vh] grid-cols-2 gap-4 overflow-y-auto p-1 sm:grid-cols-3 lg:grid-cols-4">
                {images.map(file => (
                    <li key={file.id} className="min-w-0">
                        <button type="button" onClick={() => handleOnImageClick(file.id)}
                            aria-pressed={selectedImages.includes(file.id)}
                            aria-label={file.alt || file.title || `Image ${file.id}`}
                            className={`relative block w-full rounded-lg border-2 p-2 text-left focus-visible:outline-2 focus-visible:outline-indigo-600 ${selectedImages.includes(file.id) ? 'border-indigo-600 bg-indigo-50' : 'border-transparent hover:bg-gray-100'}`}>
                            <img src={file.thumbnail_path || file.path} alt=""
                                className="aspect-[4/5] w-full rounded-md bg-gray-100 object-contain" />
                            {selectedImages.includes(file.id) && (
                                <CheckCircleIcon className="absolute right-3 top-3 h-7 w-7 rounded-full bg-white text-green-600" aria-hidden="true" />
                            )}
                            <span className="mt-2 block truncate text-sm font-medium text-gray-900">{file.alt || file.title}</span>
                            <span className="block text-sm text-gray-500">{file.size}</span>
                        </button>
                    </li>
                ))}
            </ul>
        )}
        <div className="mt-4 flex justify-end border-t border-gray-200 pt-4">
            <button type="button" onClick={() => setOpen(false)}
                className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-indigo-600">
                Done
            </button>
        </div>
    </MainModal>
);

export default ChoseMainImageModal;
