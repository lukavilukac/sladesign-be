import { Dialog, DialogBackdrop, DialogPanel, DialogTitle } from '@headlessui/react';

const ImageUploadModal = ({ open, setOpen, children }) => (
    <Dialog open={open} onClose={setOpen} className="relative z-50">
        <DialogBackdrop className="fixed inset-0 bg-black/50" />
        <div className="fixed inset-0 overflow-y-auto p-4">
            <div className="flex min-h-full items-center justify-center">
                <DialogPanel className="relative w-full max-w-3xl rounded-lg bg-white p-6 shadow-xl">
                    <DialogTitle className="mb-4 text-lg font-semibold text-gray-900">
                        Upload Image
                    </DialogTitle>
                    {children}
                </DialogPanel>
            </div>
        </div>
    </Dialog>
);

export default ImageUploadModal;
