import OrderCard from "@/Components/OrderCard.jsx";
import {Draggable, Droppable} from "@hello-pangea/dnd";

export default function Column({title, items, id}) {

    return (
        <Droppable droppableId={id}>
            {

                (provided) => {
                    return <div className={`bg-gray-100 shadow-sm w-1/5`}>
                        <div className="border-b border-gray-200 bg-indigo-600 px-4 py-5 sm:px-6">
                            <div className="-ml-4 -mt-2 flex flex-wrap items-center justify-between sm:flex-nowrap">
                                <div className="ml-4 mt-2">
                                    <h3 className="text-base font-semibold leading-6 text-white">{title}</h3>
                                </div>
                            </div>
                        </div>
                        <ul style={{ minHeight: "100%" }} ref={provided.innerRef} {...provided.droppableProps} role="list" className="space-y-3 mt-4 pb-8 pl-8 pr-8">
                            {items.map((item, index) => (
                                <Draggable key={item.id} draggableId={item.id} index={index}>
                                    {
                                        (draggableProvided) => (

                                            <li key={item.id}
                                                {...draggableProvided.draggableProps}
                                                {...draggableProvided.dragHandleProps}
                                                ref={draggableProvided.innerRef}
                                            >
                                                <OrderCard order={item} />
                                            </li>

                                        )
                                    }
                                </Draggable>
                            ))}
                            {provided.placeholder}
                        </ul>
                    </div>
                }
            }
        </Droppable>
    )
}
